<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\University>
 */
class UniversityFactory extends Factory
{
    protected $model = University::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' University',
            'location' => fake()->city() . ', ' . fake()->country(),
            'is_active' => true,
        ];
    }

    /**
     * Create an inactive university.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}