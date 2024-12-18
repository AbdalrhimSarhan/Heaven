<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
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
            'name_ar' => $fakerAr->words(2, true), // Generates a phrase in Arabic
            'name_en' => fake()->words(2, true),        // Generates a phrase in English
            'location_ar' => $fakerAr->address(),     // Generates an Arabic address
            'location_en' => fake()->address(),            // Generates an English address
            'category_id' => Category::inRandomOrder()->first()->id, // Pick a random category
            'image' => $this->faker->imageUrl(640, 480, 'business', true, 'store'),
        ];
    }
}
