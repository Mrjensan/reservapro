<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true) . ' Service',
            'description' => fake()->sentence(12),
            'duration_minutes' => fake()->numberBetween(30, 180),
            'price' => fake()->randomFloat(2, 80, 800),
            'is_active' => true,
        ];
    }
}
