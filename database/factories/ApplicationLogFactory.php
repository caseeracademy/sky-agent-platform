<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationLog>
 */
class ApplicationLogFactory extends Factory
{
    protected $model = ApplicationLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'user_id' => User::factory()->create(['role' => 'agent_owner']),
            'note' => fake()->sentence(),
            'status_change' => fake()->optional()->randomElement([
                'pending -> submitted',
                'submitted -> under_review',
                'under_review -> approved',
                'under_review -> rejected',
            ]),
        ];
    }

    /**
     * Create a creation log entry.
     */
    public function creation(): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => 'Application created',
            'status_change' => null,
        ]);
    }

    /**
     * Create a status change log entry.
     */
    public function statusChange(string $from, string $to): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => "Status changed from {$from} to {$to}",
            'status_change' => "{$from} -> {$to}",
        ]);
    }
}