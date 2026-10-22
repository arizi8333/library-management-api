<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes (no token required): register, login.
| All other routes are protected by the `auth:sanctum` middleware and
| require a valid bearer token obtained from the login endpoint.
|
*/

// Simple API metadata / health endpoint.
Route::get('/', fn () => response()->json([
    'success' => true,
    'message' => 'Library Management API',
    'data' => ['version' => '1.0.0'],
]));

// Public auth routes.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Feature module routes are registered here in their respective PRs.
});
