<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::latest()->paginate(15);

        return $this->successResponse(
            CategoryResource::collection($categories)->response()->getData(true),
            'Categories retrieved.'
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'Category created.',
            201
        );
    }

    public function show(Category $category): JsonResponse
    {
        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved.'
        );
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated.'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->successResponse(null, 'Category deleted.');
    }
}
