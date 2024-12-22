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
            ['name_en' => 'Restaurant', 'name_ar' => 'مطاعم', 'image' => 'images/restaurant.jpg'],
            ['name_en' => 'Perfumes', 'name_ar' => 'عطور', 'image' => 'images/perfumes.jpg'],
            ['name_en' => 'Clothes', 'name_ar' => 'ملابس', 'image' => 'images/clothes.jpg'],
            ['name_en' => 'Electronics', 'name_ar' => 'إلكترونيات', 'image' => 'images/electronics.jpg'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name_en' => $category['name_en'],
                'name_ar' => $category['name_ar'],
                'image'   => $category['image'], // Adding the image field
            ]);
        }
    }

}
