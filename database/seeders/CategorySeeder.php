<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiction', 'description' => 'Novels and short stories.'],
            ['name' => 'Non-Fiction', 'description' => 'Factual and reference books.'],
            ['name' => 'Technology', 'description' => 'Programming and computing.'],
            ['name' => 'Science', 'description' => 'Physics, biology, and more.'],
            ['name' => 'History', 'description' => 'Historical works.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
