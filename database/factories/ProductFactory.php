<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        // Get all images in the 'Store_image' directory
        $storesImages = Storage::disk('public')->files('Store_image');

        // Pick a random image or fallback to placeholder/default
        $randomImage = !empty($storesImages)
            ? $storesImages[array_rand($storesImages)] // Pick a random image
            : 'Store_image/placeholder.jpg'; // Fallback to placeholder image

        if (!Storage::disk('public')->exists($randomImage)) {
            $randomImage = 'Store_image/default.jpg'; // Fallback to 'default.jpg'
        }

        return [
            'name_en' => $this->faker->word,
            'name_ar' => $this->faker->word,
            'description_en' => $this->faker->sentence,
            'description_ar' => $this->faker->sentence,
            'image' => $randomImage, // Store only the file path
        ];
    }



}
