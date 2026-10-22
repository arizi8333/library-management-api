<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Thrown when a borrowing or returning rule is violated.
 * Rendered as HTTP 422 by the global exception handler.
 */
class LoanException extends HttpException
{
    public function __construct(string $message, int $statusCode = 422)
    {
        parent::__construct($statusCode, $message);
    }
}
