<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $agent = User::factory()->create(['role' => 'agent_owner']);
        $student = Student::factory()->forAgent($agent)->create();
        $program = Program::factory()->create();

        return [
            'agent_id' => $agent->id,
            'student_id' => $student->id,
            'program_id' => $program->id,
            'status' => fake()->randomElement(['pending', 'submitted', 'under_review', 'approved']),
            'notes' => fake()->optional()->paragraph(),
            'intake_date' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'commission_amount' => fake()->randomFloat(2, 500, 10000),
            'commission_paid' => fake()->boolean(20), // 20% chance of being paid
        ];
    }

    /**
     * Create an application for a specific agent.
     */
    public function forAgent(User $agent): static
    {
        return $this->state(fn (array $attributes) => [
            'agent_id' => $agent->id,
        ]);
    }

    /**
     * Create an application with a specific status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'submitted_at' => in_array($status, ['submitted', 'under_review', 'approved', 'rejected']) ? now() : null,
        ]);
    }

    /**
     * Create a pending application.
     */
    public function pending(): static
    {
        return $this->withStatus('pending');
    }

    /**
     * Create a submitted application.
     */
    public function submitted(): static
    {
        return $this->withStatus('submitted');
    }

    /**
     * Create an approved application.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'submitted_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(2),
            'decision_at' => now()->subDays(2),
        ]);
    }
}