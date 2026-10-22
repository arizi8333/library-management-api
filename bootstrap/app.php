<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render all API exceptions as a consistent JSON envelope.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $payload = fn (string $message, int $status, mixed $errors = null) => response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], $status);

            if ($e instanceof ValidationException) {
                return $payload('The given data was invalid.', 422, $e->errors());
            }

            if ($e instanceof AuthenticationException) {
                return $payload('Unauthenticated.', 401);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return $payload('Resource not found.', 404);
            }

            if ($e instanceof HttpExceptionInterface) {
                return $payload($e->getMessage() ?: 'HTTP error.', $e->getStatusCode());
            }

            return $payload(
                app()->hasDebugModeEnabled() ? $e->getMessage() : 'Server error.',
                500
            );
        });
    })->create();
