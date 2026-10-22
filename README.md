# Library Management API

A RESTful API built with **Laravel 11** for managing a small library: book
categories, books (with stock), and borrowing/returning books. Authentication
is handled with **Laravel Sanctum** (bearer tokens).

## Features

- **Auth** — register, login, logout, current user (`/me`) using Sanctum tokens.
- **Categories** — full CRUD for book categories.
- **Books** — full CRUD for books, each belonging to a category and tracking stock.
- **Loans** — borrow a book, return a book, and list the books you are currently borrowing.

### Business rules

- All endpoints require authentication except `register` and `login`.
- A book can only be borrowed while its `stock > 0`. Borrowing decreases stock
  by 1; returning increases it by 1.
- A user may hold at most **3 active loans** at a time.
- A user cannot borrow a title they are currently borrowing; it must be
  returned first.

## Requirements

- PHP >= 8.2
- Composer
- MySQL 8

## Setup

```bash
# 1. Install dependencies
composer install

# 2. Create your environment file
cp .env.example .env
php artisan key:generate

# 3. Run migrations (and seeders for sample data)
php artisan migrate --seed

# 4. Serve the API
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

## API documentation (Swagger)

Interactive docs are available at:

```
http://127.0.0.1:8000/api/documentation
```

Log in via the `/api/login` endpoint, copy the token, click **Authorize**,
and paste it to try the protected endpoints straight from the browser.

## Authentication

1. `POST /api/register` or `POST /api/login` to obtain a token.
2. Send the token on every protected request:

```
Authorization: Bearer <your-token>
Accept: application/json
```

## API endpoints

| Method | Endpoint | Auth | Description |
| ------ | -------- | ---- | ----------- |
| POST | `/api/register` | No | Register a new user |
| POST | `/api/login` | No | Log in and receive a token |
| POST | `/api/logout` | Yes | Revoke the current token |
| GET | `/api/me` | Yes | Get the authenticated user |
| GET | `/api/categories` | Yes | List categories |
| POST | `/api/categories` | Yes | Create a category |
| GET | `/api/categories/{id}` | Yes | Show a category |
| PUT/PATCH | `/api/categories/{id}` | Yes | Update a category |
| DELETE | `/api/categories/{id}` | Yes | Delete a category |
| GET | `/api/books` | Yes | List books |
| POST | `/api/books` | Yes | Create a book |
| GET | `/api/books/{id}` | Yes | Show a book |
| PUT/PATCH | `/api/books/{id}` | Yes | Update a book |
| DELETE | `/api/books/{id}` | Yes | Delete a book |
| POST | `/api/loans` | Yes | Borrow a book |
| PUT | `/api/loans/{id}/return` | Yes | Return a borrowed book |
| GET | `/api/loans` | Yes | List the books you are currently borrowing |

## Testing

Unit tests cover the loan rules: stock, the 3-book limit, duplicate titles, and returns.

Tests use a separate database. Create it once:

```sql
CREATE DATABASE library_management_test;
GRANT ALL PRIVILEGES ON library_management_test.* TO 'library'@'localhost';
FLUSH PRIVILEGES;
```

Run:

```bash
php artisan test

# just the loan tests
php artisan test tests/Unit/LoanServiceTest.php
```

## Tech stack

- Laravel 11
- Laravel Sanctum (token authentication)
- MySQL 8
