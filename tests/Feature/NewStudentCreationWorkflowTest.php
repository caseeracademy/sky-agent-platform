<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewStudentCreationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected University $university;

    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->agent = User::factory()->create(['role' => 'agent_owner']);
        $this->university = University::factory()->create();
        $this->program = Program::factory()->create(['university_id' => $this->university->id]);
    }

    /**
     * Test that student model supports new fields
     */
    public function test_student_model_supports_new_fields(): void
    {
        $student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Smith',
            'passport_number' => 'AB1234567',
            'mothers_name' => 'Jane Smith',
            'nationality' => 'American',
        ]);

        $this->assertEquals('John', $student->first_name);
        $this->assertEquals('Michael', $student->middle_name);
        $this->assertEquals('Smith', $student->last_name);
        $this->assertEquals('AB1234567', $student->passport_number);
        $this->assertEquals('Jane Smith', $student->mothers_name);
        $this->assertEquals('American', $student->nationality);
        $this->assertEquals('John Michael Smith', $student->name); // Backward compatibility
    }

    /**
     * Test creating a student with new fields using factory
     */
    public function test_can_create_student_with_new_fields(): void
    {
        $student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'passport_number' => 'CD9876543',
            'mothers_name' => 'Mary Doe',
            'nationality' => 'Canadian',
        ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'passport_number' => 'CD9876543',
            'mothers_name' => 'Mary Doe',
            'nationality' => 'Canadian',
            'agent_id' => $this->agent->id,
        ]);

        $this->assertEquals('Jane Doe', $student->name); // No middle name
    }

    /**
     * Test that passport number uniqueness works
     */
    public function test_passport_number_uniqueness(): void
    {
        // Create first student
        Student::factory()->create([
            'agent_id' => $this->agent->id,
            'passport_number' => 'AB1234567',
        ]);

        // Try to create second student with same passport number
        $this->expectException(\Illuminate\Database\QueryException::class);

        Student::factory()->create([
            'agent_id' => $this->agent->id,
            'passport_number' => 'AB1234567', // Same passport number
        ]);
    }

    /**
     * Test that student documents can be created with new types
     */
    public function test_can_create_student_documents_with_new_types(): void
    {
        $student = Student::factory()->create(['agent_id' => $this->agent->id]);

        $documentTypes = ['passport', 'diploma', 'transcript'];

        foreach ($documentTypes as $type) {
            $document = StudentDocument::factory()->create([
                'student_id' => $student->id,
                'uploaded_by' => $this->agent->id,
                'type' => $type,
                'name' => ucfirst($type).' Document',
            ]);

            $this->assertDatabaseHas('student_documents', [
                'student_id' => $student->id,
                'type' => $type,
                'name' => ucfirst($type).' Document',
            ]);
        }

        // Test the hasDocumentType method
        $this->assertTrue($student->hasDocumentType('passport'));
        $this->assertTrue($student->hasDocumentType('diploma'));
        $this->assertTrue($student->hasDocumentType('transcript'));
        $this->assertFalse($student->hasDocumentType('other'));
    }

    /**
     * Test that applications can be created for students
     */
    public function test_can_create_application_for_student(): void
    {
        $student = Student::factory()->create(['agent_id' => $this->agent->id]);

        $application = Application::factory()->create([
            'student_id' => $student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('applications', [
            'student_id' => $student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($student->applications->contains($application));
    }

    /**
     * Test the full workflow: student + documents + application
     */
    public function test_full_workflow_student_documents_application(): void
    {
        // Create student with new fields
        $student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Smith',
            'passport_number' => 'AB1234567',
            'mothers_name' => 'Jane Smith',
            'nationality' => 'American',
        ]);

        // Create documents
        $documentTypes = ['passport', 'diploma', 'transcript'];
        foreach ($documentTypes as $type) {
            StudentDocument::factory()->create([
                'student_id' => $student->id,
                'uploaded_by' => $this->agent->id,
                'type' => $type,
                'name' => ucfirst($type).' Document',
            ]);
        }

        // Create application
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        // Verify everything is connected properly
        $this->assertEquals('John Michael Smith', $student->name);
        $this->assertEquals(3, $student->documents()->count());
        $this->assertEquals(1, $student->applications()->count());
        $this->assertEquals($application->id, $student->applications->first()->id);
        $this->assertEquals($this->program->id, $application->program_id);
    }

    /**
     * Test student name generation with and without middle name
     */
    public function test_student_name_generation(): void
    {
        // Student with middle name
        $studentWithMiddle = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Smith',
        ]);
        $this->assertEquals('John Michael Smith', $studentWithMiddle->name);

        // Student without middle name
        $studentWithoutMiddle = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Doe',
        ]);
        $this->assertEquals('Jane Doe', $studentWithoutMiddle->name);
    }
}
