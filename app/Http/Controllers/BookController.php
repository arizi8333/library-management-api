<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/books',
        summary: 'List books',
        security: [['bearerAuth' => []]],
        tags: ['Books'],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(Request $request): JsonResponse
    {
        $books = Book::with('category')
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            BookResource::collection($books)->response()->getData(true),
            'Books retrieved.'
        );
    }

    #[OA\Post(
        path: '/api/books',
        summary: 'Create a book',
        security: [['bearerAuth' => []]],
        tags: ['Books'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['category_id', 'title', 'author', 'stock'],
                properties: [
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Clean Code'),
                    new OA\Property(property: 'author', type: 'string', example: 'Robert C. Martin'),
                    new OA\Property(property: 'isbn', type: 'string', example: '9780132350884', nullable: true),
                    new OA\Property(property: 'stock', type: 'integer', example: 5),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(BookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return $this->successResponse(
            new BookResource($book->load('category')),
            'Book created.',
            201
        );
    }

    #[OA\Get(
        path: '/api/books/{id}',
        summary: 'Show a book',
        security: [['bearerAuth' => []]],
        tags: ['Books'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Book $book): JsonResponse
    {
        return $this->successResponse(
            new BookResource($book->load('category')),
            'Book retrieved.'
        );
    }

    #[OA\Put(
        path: '/api/books/{id}',
        summary: 'Update a book',
        security: [['bearerAuth' => []]],
        tags: ['Books'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Clean Code'),
                    new OA\Property(property: 'author', type: 'string', example: 'Robert C. Martin'),
                    new OA\Property(property: 'isbn', type: 'string', nullable: true),
                    new OA\Property(property: 'stock', type: 'integer', example: 3),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return $this->successResponse(
            new BookResource($book->load('category')),
            'Book updated.'
        );
    }

    #[OA\Delete(
        path: '/api/books/{id}',
        summary: 'Delete a book',
        security: [['bearerAuth' => []]],
        tags: ['Books'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return $this->successResponse(null, 'Book deleted.');
    }
}
