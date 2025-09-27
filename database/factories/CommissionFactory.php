<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Commission>
 */
class CommissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'agent_id' => User::factory()->state(['role' => 'agent_owner']),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
        ];
    }
}
