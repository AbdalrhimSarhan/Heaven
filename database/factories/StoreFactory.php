<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'category_id' => Category::inRandomOrder()->first()->id, // Pick a random category
            'name_en' => $this->faker->name,
            'name_ar' => $this->faker->name,
            'image' => $this->faker->imageUrl(640, 480, 'business', true, 'store'),
            'location_en' => $this->faker->address,
            'location_ar' => $this->faker->address,
        ];
    }
}
