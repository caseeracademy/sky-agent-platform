<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDocumentReplaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected Student $student;

    protected StudentDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->agent = User::factory()->create(['role' => 'agent_owner']);

        // Create test student
        $this->student = Student::factory()->create(['agent_id' => $this->agent->id]);

        // Create test document
        $this->document = StudentDocument::factory()->create([
            'student_id' => $this->student->id,
            'uploaded_by' => $this->agent->id,
        ]);

        Storage::fake('public');
    }

    /**
     * Test simple file replace functionality
     */
    public function test_agent_can_replace_student_document_with_simple_file_upload(): void
    {
        $this->actingAs($this->agent);

        $file = UploadedFile::fake()->create('new-document.pdf', 2048);

        $response = $this->putJson("/agent/students/{$this->student->id}/documents/{$this->document->id}/replace", [
            'document_file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Document replaced successfully.',
        ]);

        // Check that the document was updated
        $this->document->refresh();
        $this->assertEquals($file->getClientOriginalName(), $this->document->file_name);
        $this->assertEquals($file->getMimeType(), $this->document->mime_type);
        $this->assertEquals($file->getSize(), $this->document->file_size);
    }

    /**
     * Test replace validation
     */
    public function test_document_replace_validation(): void
    {
        $this->actingAs($this->agent);

        // Test missing file
        $response = $this->putJson("/agent/students/{$this->student->id}/documents/{$this->document->id}/replace", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);

        // Test invalid file type
        $file = UploadedFile::fake()->create('test.txt', 1024);

        $response = $this->putJson("/agent/students/{$this->student->id}/documents/{$this->document->id}/replace", [
            'document_file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);

        // Test file too large
        $file = UploadedFile::fake()->create('large-file.pdf', 11264); // 11MB

        $response = $this->putJson("/agent/students/{$this->student->id}/documents/{$this->document->id}/replace", [
            'document_file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);
    }

    /**
     * Test agent cannot replace documents for other students
     */
    public function test_agent_cannot_replace_documents_for_other_students(): void
    {
        $otherAgent = User::factory()->create(['role' => 'agent_owner']);
        $otherStudent = Student::factory()->create(['agent_id' => $otherAgent->id]);
        $otherDocument = StudentDocument::factory()->create([
            'student_id' => $otherStudent->id,
            'uploaded_by' => $otherAgent->id,
        ]);

        $this->actingAs($this->agent);

        $file = UploadedFile::fake()->create('new-document.pdf', 2048);

        $response = $this->putJson("/agent/students/{$otherStudent->id}/documents/{$otherDocument->id}/replace", [
            'document_file' => $file,
        ]);

        $response->assertStatus(403);
    }
}
