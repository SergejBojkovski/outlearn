<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Science'],
            ['name' => 'Cloud Computing'],
            ['name' => 'Mathematics'],
            ['name' => 'Reading'],
            ['name' => 'Programming'],
            ['name' => 'Literature'],
            ['name' => 'Design'],
            ['name' => 'Data Science'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
