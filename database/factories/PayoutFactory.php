<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payout>
 */
class PayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => \App\Models\User::factory()->state(['role' => 'agent_owner']),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $this->faker->randomElement(['pending', 'paid', 'rejected']),
        ];
    }
}
