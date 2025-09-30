<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentCreationWithDocumentsTest extends TestCase
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
     * Test creating a student without application (documents only)
     */
    public function test_can_create_student_without_application(): void
    {
        // Create student directly (without Filament page logic)
        $student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'passport_number' => 'AB1234567',
            'mothers_name' => 'Jane Doe',
            'nationality' => 'American',
            'email' => 'john.doe@example.com',
            'date_of_birth' => '1995-01-01',
        ]);

        // Create student documents manually
        $documentTypes = ['passport', 'diploma', 'transcript'];
        foreach ($documentTypes as $type) {
            StudentDocument::factory()->create([
                'student_id' => $student->id,
                'uploaded_by' => $this->agent->id,
                'type' => $type,
                'name' => ucfirst($type).' Document',
            ]);
        }

        // Student should be created
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'passport_number' => 'AB1234567',
        ]);

        // No application should be created
        $this->assertDatabaseMissing('applications', [
            'student_id' => $student->id,
        ]);

        // Student documents should be created
        $this->assertEquals(3, $student->documents()->count());
        $this->assertTrue($student->hasDocumentType('passport'));
        $this->assertTrue($student->hasDocumentType('diploma'));
        $this->assertTrue($student->hasDocumentType('transcript'));
    }

    /**
     * Test creating a student with application (documents in both places)
     */
    public function test_can_create_student_with_application_and_documents(): void
    {
        // Create student directly
        $student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'passport_number' => 'CD9876543',
            'mothers_name' => 'Mary Smith',
            'nationality' => 'Canadian',
            'email' => 'jane.smith@example.com',
            'date_of_birth' => '1995-02-01',
        ]);

        // Create application
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        // Create student documents
        $documentTypes = ['passport', 'diploma', 'transcript'];
        foreach ($documentTypes as $type) {
            StudentDocument::factory()->create([
                'student_id' => $student->id,
                'uploaded_by' => $this->agent->id,
                'type' => $type,
                'name' => ucfirst($type).' Document',
            ]);
        }

        // Create application documents
        foreach ($documentTypes as $type) {
            ApplicationDocument::create([
                'application_id' => $application->id,
                'uploaded_by_user_id' => $this->agent->id,
                'title' => ucfirst($type).' Document',
                'original_filename' => "{$type}.pdf",
                'disk' => 'public',
                'path' => "documents/{$type}.pdf",
                'file_size' => 1000,
                'mime_type' => 'application/pdf',
            ]);
        }

        // Student should be created
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'passport_number' => 'CD9876543',
        ]);

        // Application should be created
        $this->assertNotNull($application);
        $this->assertDatabaseHas('applications', [
            'student_id' => $student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        // Student documents should be created
        $this->assertEquals(3, $student->documents()->count());
        $this->assertTrue($student->hasDocumentType('passport'));
        $this->assertTrue($student->hasDocumentType('diploma'));
        $this->assertTrue($student->hasDocumentType('transcript'));

        // Application documents should also be created
        $this->assertEquals(3, $application->applicationDocuments()->count());

        $appDocs = $application->applicationDocuments;
        $this->assertTrue($appDocs->contains('title', 'Passport Document'));
        $this->assertTrue($appDocs->contains('title', 'Diploma Document'));
        $this->assertTrue($appDocs->contains('title', 'Transcript Document'));
    }

    /**
     * Test that passport number uniqueness is enforced
     */
    public function test_passport_number_uniqueness_enforced(): void
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
     * Test that student name is properly generated from separate fields
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

    /**
     * Test that documents are properly linked to applications
     */
    public function test_documents_linked_to_both_student_and_application(): void
    {
        $student = Student::factory()->create(['agent_id' => $this->agent->id]);
        $application = Application::factory()->create([
            'student_id' => $student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
        ]);

        // Create student document
        $studentDoc = StudentDocument::factory()->create([
            'student_id' => $student->id,
            'uploaded_by' => $this->agent->id,
            'type' => 'passport',
        ]);

        // Create application document with same file
        $appDoc = ApplicationDocument::create([
            'application_id' => $application->id,
            'uploaded_by_user_id' => $this->agent->id,
            'title' => 'Passport Document',
            'original_filename' => 'passport.pdf',
            'disk' => 'public',
            'path' => $studentDoc->file_path,
            'file_size' => 1000,
            'mime_type' => 'application/pdf',
        ]);

        // Both should exist and be linked properly
        $this->assertTrue($student->documents->contains($studentDoc));
        $this->assertTrue($application->applicationDocuments->contains($appDoc));
        $this->assertEquals($studentDoc->file_path, $appDoc->path);
    }
}
