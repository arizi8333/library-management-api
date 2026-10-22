<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Book;
use App\Models\Loan;
use App\Services\LoanService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LoanService $loans)
    {
    }

    /**
     * List the books the authenticated user is currently borrowing.
     */
    public function index(Request $request): JsonResponse
    {
        $loans = $request->user()
            ->loans()
            ->active()
            ->with('book.category')
            ->latest('borrowed_at')
            ->get();

        return $this->successResponse(
            LoanResource::collection($loans),
            'Active loans retrieved.'
        );
    }

    /**
     * Borrow a book.
     */
    public function store(StoreLoanRequest $request): JsonResponse
    {
        $book = Book::findOrFail($request->integer('book_id'));

        $loan = $this->loans->borrow($request->user(), $book);

        return $this->successResponse(
            new LoanResource($loan->load('book.category')),
            'Book borrowed.',
            201
        );
    }

    /**
     * Return a borrowed book.
     */
    public function returnBook(Request $request, Loan $loan): JsonResponse
    {
        $loan = $this->loans->returnBook($request->user(), $loan);

        return $this->successResponse(
            new LoanResource($loan->load('book.category')),
            'Book returned.'
        );
    }
}
