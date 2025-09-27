<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => User::factory()->create(['role' => 'agent_owner']),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'country_of_residence' => fake()->randomElement([
                'United States',
                'Canada',
                'United Kingdom',
                'Australia',
                'India',
                'China',
                'Germany',
                'France',
                'Brazil',
                'Mexico',
            ]),
            'date_of_birth' => fake()->dateTimeBetween('-30 years', '-16 years')->format('Y-m-d'),
        ];
    }

    /**
     * Create a student for a specific agent.
     */
    public function forAgent(User $agent): static
    {
        return $this->state(fn (array $attributes) => [
            'agent_id' => $agent->id,
        ]);
    }

    /**
     * Create a student from a specific country.
     */
    public function fromCountry(string $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country_of_residence' => $country,
        ]);
    }

    /**
     * Create a student with a specific age.
     */
    public function aged(int $years): static
    {
        return $this->state(fn (array $attributes) => [
            'date_of_birth' => now()->subYears($years)->format('Y-m-d'),
        ]);
    }
}