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
        $categories = ['Restaurant', 'Perfumes', 'Clothes', 'Electronics'];
        $categories_ar = ['مطاعم', 'عطور', 'ملابس', 'إلكترونيات'];

        foreach ($categories as $index => $categoryName) {
            Category::create([
                'name_en' => $categoryName,            // English name
                'name_ar' => $categories_ar[$index],   // Arabic name
            ]);
        }
    }
}
