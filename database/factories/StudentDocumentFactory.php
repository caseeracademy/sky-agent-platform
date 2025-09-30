<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentDocument>
 */
class StudentDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $file = UploadedFile::fake()->create('test-document.pdf', 1024);
        $filePath = $file->store('student-documents', 'public');

        return [
            'student_id' => Student::factory(),
            'uploaded_by' => User::factory(),
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['passport', 'certificate', 'transcript', 'photo', 'visa', 'language_test', 'other']),
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'description' => fake()->sentence(),
        ];
    }
}
