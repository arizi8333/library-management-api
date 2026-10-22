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
use OpenApi\Attributes as OA;

class LoanController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LoanService $loans)
    {
    }

    #[OA\Get(
        path: '/api/loans',
        summary: 'List the books you are currently borrowing',
        security: [['bearerAuth' => []]],
        tags: ['Loans'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
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

    #[OA\Post(
        path: '/api/loans',
        summary: 'Borrow a book',
        security: [['bearerAuth' => []]],
        tags: ['Loans'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['book_id'],
                properties: [new OA\Property(property: 'book_id', type: 'integer', example: 1)]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Borrowed'),
            new OA\Response(response: 422, description: 'Rule violation or validation error'),
        ]
    )]
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

    #[OA\Put(
        path: '/api/loans/{id}/return',
        summary: 'Return a borrowed book',
        security: [['bearerAuth' => []]],
        tags: ['Loans'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Returned'),
            new OA\Response(response: 403, description: 'Not your loan'),
            new OA\Response(response: 422, description: 'Already returned'),
        ]
    )]
    public function returnBook(Request $request, Loan $loan): JsonResponse
    {
        $loan = $this->loans->returnBook($request->user(), $loan);

        return $this->successResponse(
            new LoanResource($loan->load('book.category')),
            'Book returned.'
        );
    }
}
