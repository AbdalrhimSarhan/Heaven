<?php

namespace Database\Factories;

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
        return [
            'name_en' => $this->faker->word,
            'name_ar' => $this->faker->word,
            'description_en' => $this->faker->sentence,
            'description_ar' => $this->faker->sentence,
            'image' => $this->faker->imageUrl(640, 480, 'product', true, 'product'),
        ];
    }
}
