<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use ApiResponse;

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

    public function store(BookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return $this->successResponse(
            new BookResource($book->load('category')),
            'Book created.',
            201
        );
    }

    public function show(Book $book): JsonResponse
    {
        return $this->successResponse(
            new BookResource($book->load('category')),
            'Book retrieved.'
        );
    }

    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return $this->successResponse(
            new BookResource($book->load('category')),
            'Book updated.'
        );
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return $this->successResponse(null, 'Book deleted.');
    }
}
