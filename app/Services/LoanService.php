<?php

namespace App\Services;

use App\Exceptions\LoanException;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Maximum number of books a user may hold at the same time.
     */
    public const MAX_ACTIVE_LOANS = 3;

    /**
     * Borrow a book for the given user.
     *
     * Rules enforced:
     *  - the book must have stock available
     *  - the user may not exceed MAX_ACTIVE_LOANS active loans
     *  - the user may not borrow a title they are already borrowing
     *
     * @throws LoanException
     */
    public function borrow(User $user, Book $book): Loan
    {
        return DB::transaction(function () use ($user, $book) {
            // Lock the book row to avoid race conditions on stock.
            $book = Book::whereKey($book->getKey())->lockForUpdate()->firstOrFail();

            $this->assertCanBorrow($user, $book);

            $book->decrement('stock');

            return Loan::create([
                'user_id' => $user->getKey(),
                'book_id' => $book->getKey(),
                'borrowed_at' => now(),
            ]);
        });
    }

    /**
     * Return a borrowed book.
     *
     * Rules enforced:
     *  - the loan must belong to the user
     *  - the loan must not already be returned
     *
     * @throws LoanException
     */
    public function returnBook(User $user, Loan $loan): Loan
    {
        return DB::transaction(function () use ($user, $loan) {
            if ($loan->user_id !== $user->getKey()) {
                throw new LoanException('This loan does not belong to you.', 403);
            }

            if ($loan->returned_at !== null) {
                throw new LoanException('This book has already been returned.');
            }

            $loan->update(['returned_at' => now()]);

            Book::whereKey($loan->book_id)->increment('stock');

            return $loan->refresh();
        });
    }

    /**
     * Validate every borrowing rule, throwing on the first violation.
     *
     * @throws LoanException
     */
    public function assertCanBorrow(User $user, Book $book): void
    {
        if ($book->stock < 1) {
            throw new LoanException('This book is out of stock.');
        }

        $activeCount = $user->loans()->active()->count();

        if ($activeCount >= self::MAX_ACTIVE_LOANS) {
            throw new LoanException(
                'You have reached the maximum of '.self::MAX_ACTIVE_LOANS.' active loans.'
            );
        }

        $alreadyBorrowing = $user->loans()
            ->active()
            ->where('book_id', $book->getKey())
            ->exists();

        if ($alreadyBorrowing) {
            throw new LoanException('You are already borrowing this book. Return it first.');
        }
    }
}
