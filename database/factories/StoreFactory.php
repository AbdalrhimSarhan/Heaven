<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        // Get all images in the 'Store_image' directory
        $storesImages = Storage::disk('public')->files('Store_image');

        // Ensure we have images and select one or use a placeholder
        $randomImage = !empty($storesImages)
            ? $storesImages[array_rand($storesImages)] // Pick a random image
            : 'Store_image/placeholder.jpg'; // Default image path

        // Generate the URL for the selected image
        $imageUrl = Storage::url($randomImage);

        return [
            'category_id' => Category::inRandomOrder()->first()->id, // Pick a random category
            'name_en' => $this->faker->name,
            'name_ar' => $this->faker->name,
            'image' => $imageUrl, // Correctly assign the full image URL
            'location_en' => $this->faker->address,
            'location_ar' => $this->faker->address,
        ];
    }


}
