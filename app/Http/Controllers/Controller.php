<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Library Management API',
    description: 'REST API for managing book categories, books, and loans.'
)]
#[OA\Server(url: 'http://127.0.0.1:8000', description: 'Local server')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
abstract class Controller
{
    //
}
