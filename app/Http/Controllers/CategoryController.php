<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/categories',
        summary: 'List categories',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(): JsonResponse
    {
        $categories = Category::latest()->paginate(15);

        return $this->successResponse(
            CategoryResource::collection($categories)->response()->getData(true),
            'Categories retrieved.'
        );
    }

    #[OA\Post(
        path: '/api/categories',
        summary: 'Create a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Fiction'),
                    new OA\Property(property: 'description', type: 'string', example: 'Novels and short stories', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'Category created.',
            201
        );
    }

    #[OA\Get(
        path: '/api/categories/{id}',
        summary: 'Show a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Category $category): JsonResponse
    {
        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved.'
        );
    }

    #[OA\Put(
        path: '/api/categories/{id}',
        summary: 'Update a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Non-Fiction'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated.'
        );
    }

    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Delete a category',
        security: [['bearerAuth' => []]],
        tags: ['Categories'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->successResponse(null, 'Category deleted.');
    }
}
