<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->info('No categories found! Please run CategorySeeder first.');
            return;
        }

        // Create stores for each category
        foreach ($categories as $category) {
            // Create 2-3 stores per category
            for ($i = 1; $i <= rand(2, 3); $i++) {
                Store::create([
                    'name_en' => "{$category->name_en} Store {$i}",
                    'name_ar' => "{$category->name_ar} متجر {$i}",
                    'location_en' => fake()->address(),
                    'location_ar' => fake()->address(),
                    'category_id' => $category->id,
                ]);
            }
        }

        $this->command->info('Stores created successfully!');
    }
}
