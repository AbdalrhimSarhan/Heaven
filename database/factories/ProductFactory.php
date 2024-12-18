<?php

namespace Database\Factories;

use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fakerAr = FakerFactory::create('ar_SA'); // Arabic Faker instance

        return [
            'name_ar' => $fakerAr->words(3, true), // Arabic product name
            'name_en' => fake()->words(3, true),        // English product name
            'description_ar' => $fakerAr->sentence(8),   // Arabic product description
            'description_en' => fake()->sentence(8),         // English product description
        ];
    }
}
