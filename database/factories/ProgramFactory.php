<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tuitionFee = fake()->randomFloat(2, 15000, 100000);
        $agentCommission = $tuitionFee * 0.10; // 10% commission
        $systemCommission = $agentCommission * 0.20; // 20% of agent commission

        return [
            'university_id' => University::factory(),
            'name' => fake()->randomElement([
                'Computer Science',
                'Business Administration',
                'Engineering',
                'Medicine',
                'Arts & Sciences',
                'Digital Marketing',
                'Data Science',
                'Nursing',
                'Law',
                'Psychology',
            ]),
            'tuition_fee' => $tuitionFee,
            'agent_commission' => $agentCommission,
            'system_commission' => $systemCommission,
            'degree_type' => fake()->randomElement(['Bachelor', 'Master with Thesis', 'Master without Thesis', 'Diploma', 'PhD']),
            'is_active' => true,
        ];
    }

    /**
     * Create a program for a specific university.
     */
    public function forUniversity(University $university): static
    {
        return $this->state(fn (array $attributes) => [
            'university_id' => $university->id,
        ]);
    }

    /**
     * Create a program with a specific degree type.
     */
    public function degreeType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'degree_type' => $type,
        ]);
    }

    /**
     * Create an inactive program.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}