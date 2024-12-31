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

        // Ensure there is at least one image, or fall back to a default placeholder
        $randomImage = !empty($storesImages)
            ? $storesImages[array_rand($storesImages)] // Pick a random image
            : 'Store_image/placeholder.jpg'; // Fallback to placeholder image

        // Check if the placeholder image exists; if not, create a valid default path
        if (!Storage::disk('public')->exists($randomImage)) {
            $randomImage = 'Store_image/default.jpg'; // Fallback to 'default.jpg'
        }

        // Generate the public URL for the selected image
        $imageUrl = Storage::url($randomImage);

        return [
            'name_en' => $this->faker->word,
            'name_ar' => $this->faker->word,
            'description_en' => $this->faker->sentence,
            'description_ar' => $this->faker->sentence,
            'image' => $imageUrl, // Correctly assign the full image URL
        ];
    }


}
