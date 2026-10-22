<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $fiction = Category::where('name', 'Fiction')->first();
        $tech = Category::where('name', 'Technology')->first();
        $science = Category::where('name', 'Science')->first();

        $books = [
            ['category_id' => $fiction?->id, 'title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata', 'isbn' => '9789793062792', 'stock' => 5],
            ['category_id' => $fiction?->id, 'title' => 'Bumi Manusia', 'author' => 'Pramoedya Ananta Toer', 'isbn' => '9789799731240', 'stock' => 3],
            ['category_id' => $tech?->id, 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9780132350884', 'stock' => 4],
            ['category_id' => $tech?->id, 'title' => 'The Pragmatic Programmer', 'author' => 'Andrew Hunt', 'isbn' => '9780201616224', 'stock' => 2],
            ['category_id' => $science?->id, 'title' => 'A Brief History of Time', 'author' => 'Stephen Hawking', 'isbn' => '9780553380163', 'stock' => 6],
        ];

        foreach ($books as $book) {
            if ($book['category_id'] === null) {
                continue;
            }

            Book::firstOrCreate(['isbn' => $book['isbn']], $book);
        }
    }
}
