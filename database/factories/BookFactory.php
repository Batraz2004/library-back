<?php

namespace Database\Factories;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => Seller::query()->first(),
            'author' => fake()->name(),
            'title' => fake()->name(),
            'isbn' => fake()->uuid(),
            'price' => fake()->randomFloat(3,250,1200),
            'count' => rand(5,30),
            'created_at' => now(),
        ];
    }
}
