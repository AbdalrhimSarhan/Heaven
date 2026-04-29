<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Execution order:
     * 1. Categories (fixed categories)
     * 2. Users (test users for authentication)
     * 3. Stores (linked to categories)
     * 4. Products (standalone products)
     * 5. Store-Product pivot (links stores and products with prices/quantities)
     */
    public function run(): void
    {
        // 1️⃣ Seed categories first
        $this->call(CategorySeeder::class);

        // 2️⃣ Seed users for testing
        $this->call(UserSeeder::class);

        // 3️⃣ Seed stores (linked to categories)
        $this->call(StoreSeeder::class);

        // 4️⃣ Seed products
        $this->call(ProductSeeder::class);

        // 5️⃣ Link stores with products (pivot table)
        $this->call(StoreProductSeeder::class);

        $this->command->info('✅ All seeders completed successfully!');
    }
}
