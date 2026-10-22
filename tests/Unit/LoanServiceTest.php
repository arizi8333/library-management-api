<?php

namespace Tests\Unit;

use App\Exceptions\LoanException;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoanService();
    }

    private function makeBook(int $stock = 1): Book
    {
        return Book::factory()->for(Category::factory())->create(['stock' => $stock]);
    }

    public function test_borrowing_decreases_stock_and_creates_a_loan(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 3);

        $loan = $this->service->borrow($user, $book);

        $this->assertNull($loan->returned_at);
        $this->assertSame(2, $book->fresh()->stock);
        $this->assertDatabaseHas('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'returned_at' => null,
        ]);
    }

    public function test_cannot_borrow_when_out_of_stock(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 0);

        $this->expectException(LoanException::class);
        $this->expectExceptionMessage('out of stock');

        $this->service->borrow($user, $book);
    }

    public function test_cannot_exceed_the_maximum_active_loans(): void
    {
        $user = User::factory()->create();

        // Fill the user's quota with three active loans.
        for ($i = 0; $i < LoanService::MAX_ACTIVE_LOANS; $i++) {
            Loan::factory()->create(['user_id' => $user->id]);
        }

        $book = $this->makeBook(stock: 5);

        $this->expectException(LoanException::class);
        $this->expectExceptionMessage('maximum');

        $this->service->borrow($user, $book);
    }

    public function test_returned_loans_do_not_count_against_the_limit(): void
    {
        $user = User::factory()->create();

        // Three loans but all already returned.
        Loan::factory()->count(3)->returned()->create(['user_id' => $user->id]);

        $book = $this->makeBook(stock: 1);

        $loan = $this->service->borrow($user, $book);

        $this->assertNull($loan->returned_at);
    }

    public function test_cannot_borrow_a_title_already_being_borrowed(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 5);

        $this->service->borrow($user, $book);

        $this->expectException(LoanException::class);
        $this->expectExceptionMessage('already borrowing');

        $this->service->borrow($user, $book);
    }

    public function test_returning_a_book_increases_stock_and_sets_returned_at(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 2);

        $loan = $this->service->borrow($user, $book);
        $this->assertSame(1, $book->fresh()->stock);

        $returned = $this->service->returnBook($user, $loan);

        $this->assertNotNull($returned->returned_at);
        $this->assertSame(2, $book->fresh()->stock);
    }

    public function test_cannot_return_a_loan_that_belongs_to_someone_else(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = $this->makeBook(stock: 2);

        $loan = $this->service->borrow($owner, $book);

        $this->expectException(LoanException::class);
        $this->expectExceptionMessage('does not belong');

        $this->service->returnBook($other, $loan);
    }

    public function test_cannot_return_a_book_twice(): void
    {
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 2);

        $loan = $this->service->borrow($user, $book);
        $this->service->returnBook($user, $loan);

        $this->expectException(LoanException::class);
        $this->expectExceptionMessage('already been returned');

        $this->service->returnBook($user, $loan->fresh());
    }
}
