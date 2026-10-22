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

# 3. Configure the database in .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=library_management
#    DB_USERNAME=library
#    DB_PASSWORD=your_password

# 4. Run migrations (and seeders for sample data)
php artisan migrate --seed

# 5. Serve the API
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`.

## Authentication

1. `POST /api/register` or `POST /api/login` to obtain a token.
2. Send the token on every protected request:

```
Authorization: Bearer <your-token>
Accept: application/json
```

## Response format

Every response uses a consistent envelope:

```json
{
  "success": true,
  "message": "OK",
  "data": { }
}
```

Errors:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": { "email": ["The email field is required."] }
}
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

## Development workflow

Each feature module is developed on its own branch and merged into `main`
through a separate pull request:

- `feature/project-setup` — Laravel + Sanctum scaffolding
- `feature/auth` — authentication module
- `feature/categories` — category CRUD
- `feature/books` — book CRUD
- `feature/loans` — borrowing and returning

## Tech stack

- Laravel 11
- Laravel Sanctum (token authentication)
- MySQL 8
