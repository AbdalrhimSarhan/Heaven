<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Seed the fixed categories
        $this->call(CategorySeeder::class);

        // Create stores with fixed categories
        $stores = Store::factory(10)->create(); // Create 10 stores

        // Create products
        $products = Product::factory(50)->create(); // Create 50 products

        // Attach products to stores with pivot data (price, quantity)
        foreach ($stores as $store) {
            $store->products()->attach(
                $products->random(rand(5, 15))->pluck('id')->toArray(), // Attach 5-15 random products
                [
                    'price' => fake()->randomFloat(2, 10, 100), // Random price
                    'quantity' => fake()->numberBetween(1, 100), // Random quantity
                ]
            );
        }
    }
}
