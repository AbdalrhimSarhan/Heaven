<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the fixed categories first
        $this->call(CategorySeeder::class);

        // Create stores with fixed categories in smaller batches
        $stores = Store::factory(50)->create(); // Create 10 stores

        // Create products one by one to avoid holding large collections in memory
        $products = Product::factory(100)->create(); // Create 50 products

        // Loop through each store and attach products in smaller chunks to avoid high memory usage
        foreach ($stores as $store) {
            $randomProducts = $products->random(rand(5, 15));

            // Attach each product in batches of 50 to avoid memory overload
            $pivotData = [];
            foreach ($randomProducts as $product) {
                $pivotData[] = [
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                    'price' => fake()->randomFloat(2, 10, 100),  // Random price
                    'quantity' => fake()->numberBetween(1, 100), // Random quantity
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insert in batches of 50
                if (count($pivotData) >= 50) {
                    DB::table('store_product')->insert($pivotData);
                    $pivotData = [];  // Reset after insert
                }
            }

            // Insert any remaining data in smaller batches
            if (!empty($pivotData)) {
                DB::table('store_product')->insert($pivotData);
            }
        }
    }
}
