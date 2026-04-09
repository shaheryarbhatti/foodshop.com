<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['title' => 'Burgers', 'permalink' => 'burgers', 'status' => true],
            ['title' => 'Pizza', 'permalink' => 'pizza', 'status' => true],
            ['title' => 'Drinks', 'permalink' => 'drinks', 'status' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['permalink' => $category['permalink']],
                $category
            );
        }
    }
}
