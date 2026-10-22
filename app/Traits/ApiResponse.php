<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Provides a consistent JSON response envelope for all API controllers.
 *
 * Every response follows the shape:
 *   { "success": bool, "message": string, "data": mixed, "errors": mixed }
 */
trait ApiResponse
{
    /**
     * Return a successful JSON response.
     */
    protected function successResponse(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message = 'Error', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
