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
            ['name_ar' => 'مطاعم', 'name_en' => 'Restaurants'],
            ['name_ar' => 'عطور', 'name_en' => 'Perfumes'],
            ['name_ar' => 'ملابس', 'name_en' => 'Clothes'],
            ['name_ar' => 'إلكترونيات', 'name_en' => 'Electronics'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
