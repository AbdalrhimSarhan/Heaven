<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Product;
use App\Models\Store_product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates the pivot relationship between stores and products
     * with realistic prices and quantities for testing concurrent operations
     */
    public function run(): void
    {
        $stores = Store::all();
        $products = Product::all();

        if ($stores->isEmpty() || $products->isEmpty()) {
            $this->command->error('Stores or Products not found! Please run StoreSeeder and ProductSeeder first.');
            return;
        }

        // Pricing for different product categories
        $prices = [
            // Food items (restaurants)
            'Cheeseburger' => 5.99,
            'Pizza Margherita' => 8.99,
            'Grilled Chicken' => 12.99,

            // Perfumes
            'Eau de Cologne' => 25.99,
            'Perfume Spray' => 45.99,
            'Essential Oil' => 15.99,

            // Clothing
            'Cotton T-Shirt' => 14.99,
            'Blue Jeans' => 49.99,
            'Black Hoodie' => 39.99,

            // Electronics
            'USB Cable' => 4.99,
            'Phone Charger' => 19.99,
            'Screen Protector' => 9.99,
        ];

        // Assign each product to multiple stores with different quantities
        foreach ($products as $product) {
            $randomStores = $stores->random(rand(3, 6)); // Each product in 3-6 stores

            foreach ($randomStores as $store) {
                // Check if this combination already exists
                $exists = Store_product::where('store_id', $store->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if (!$exists) {
                    // Set high quantities for testing concurrent operations
                    $quantity = rand(50, 200); // High initial quantities

                    $price = $prices[$product->name_en] ?? fake()->randomFloat(2, 5, 100);

                    Store_product::create([
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'price' => $price,
                        'quantity' => $quantity,
                    ]);
                }
            }
        }

        $this->command->info('Store-Product relationships created successfully!');
        $this->command->info('Total products linked: ' . Store_product::count());
    }
}
