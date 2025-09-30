<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected User $superAdmin;

    protected Student $student;

    protected StudentDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->agent = User::factory()->create(['role' => 'agent_owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

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
     * Test agent can upload student documents
     */
    public function test_agent_can_upload_student_documents(): void
    {
        $this->actingAs($this->agent);

        $file = UploadedFile::fake()->create('test-document.pdf', 1024);

        $response = $this->postJson("/agent/students/{$this->student->id}/documents", [
            'document_name' => 'Test Document',
            'document_type' => 'passport',
            'document_file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Document uploaded successfully.',
        ]);

        $this->assertDatabaseHas('student_documents', [
            'student_id' => $this->student->id,
            'name' => 'Test Document',
            'type' => 'passport',
            'uploaded_by' => $this->agent->id,
        ]);
    }

    /**
     * Test agent can replace student documents
     */
    public function test_agent_can_replace_student_documents(): void
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

        // Check that the document was updated with new file details but kept original name and type
        $this->document->refresh();
        $this->assertEquals($file->getClientOriginalName(), $this->document->file_name);
        $this->assertEquals($file->getMimeType(), $this->document->mime_type);
        $this->assertEquals($file->getSize(), $this->document->file_size);
    }

    /**
     * Test agent can download student documents
     */
    public function test_agent_can_download_student_documents(): void
    {
        $this->actingAs($this->agent);

        // Create a real file for testing
        $file = UploadedFile::fake()->create('test-download.pdf', 1024);
        $filePath = $file->store('student-documents', 'public');

        $document = StudentDocument::factory()->create([
            'student_id' => $this->student->id,
            'file_path' => $filePath,
            'file_name' => 'test-download.pdf',
        ]);

        $response = $this->get("/agent/students/{$this->student->id}/documents/{$document->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=test-download.pdf');
    }

    /**
     * Test super admin gets correct download URL
     */
    public function test_super_admin_gets_admin_download_url(): void
    {
        $this->actingAs($this->superAdmin);

        $downloadUrl = $this->document->download_url;

        $this->assertStringContainsString('/storage/student-documents/', $downloadUrl);
        $this->assertStringNotContainsString('/agent/', $downloadUrl);
    }

    /**
     * Test agent gets correct download URL
     */
    public function test_agent_gets_agent_download_url(): void
    {
        $this->actingAs($this->agent);

        $downloadUrl = $this->document->download_url;

        $this->assertStringContainsString('/agent/students/', $downloadUrl);
        $this->assertStringContainsString('/documents/', $downloadUrl);
        $this->assertStringContainsString('/download', $downloadUrl);
    }

    /**
     * Test agent cannot upload documents for other agents' students
     */
    public function test_agent_cannot_upload_documents_for_other_students(): void
    {
        $otherAgent = User::factory()->create(['role' => 'agent_owner']);
        $otherStudent = Student::factory()->create(['agent_id' => $otherAgent->id]);

        $this->actingAs($this->agent);

        $file = UploadedFile::fake()->create('test-document.pdf', 1024);

        $response = $this->postJson("/agent/students/{$otherStudent->id}/documents", [
            'document_name' => 'Test Document',
            'document_type' => 'passport',
            'document_file' => $file,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test agent cannot replace documents for other agents' students
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

    /**
     * Test agent cannot download documents for other agents' students
     */
    public function test_agent_cannot_download_documents_for_other_students(): void
    {
        $otherAgent = User::factory()->create(['role' => 'agent_owner']);
        $otherStudent = Student::factory()->create(['agent_id' => $otherAgent->id]);
        $otherDocument = StudentDocument::factory()->create([
            'student_id' => $otherStudent->id,
            'uploaded_by' => $otherAgent->id,
        ]);

        $this->actingAs($this->agent);

        $response = $this->get("/agent/students/{$otherStudent->id}/documents/{$otherDocument->id}/download");

        $response->assertStatus(403);
    }

    /**
     * Test document validation for upload
     */
    public function test_document_upload_validation(): void
    {
        $this->actingAs($this->agent);

        // Test missing required fields
        $response = $this->postJson("/agent/students/{$this->student->id}/documents", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);

        // Test invalid file type
        $file = UploadedFile::fake()->create('test.txt', 1024);

        $response = $this->postJson("/agent/students/{$this->student->id}/documents", [
            'document_name' => 'Test Document',
            'document_type' => 'passport',
            'document_file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);

        // Test file too large
        $file = UploadedFile::fake()->create('large-file.pdf', 11264); // 11MB

        $response = $this->postJson("/agent/students/{$this->student->id}/documents", [
            'document_name' => 'Test Document',
            'document_type' => 'passport',
            'document_file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);
    }

    /**
     * Test document validation for replace
     */
    public function test_document_replace_validation(): void
    {
        $this->actingAs($this->agent);

        // Test missing required fields
        $response = $this->putJson("/agent/students/{$this->student->id}/documents/{$this->document->id}/replace", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_file']);
    }

    /**
     * Test document file size formatting
     */
    public function test_document_file_size_formatting(): void
    {
        $document = StudentDocument::factory()->create([
            'file_size' => 1536, // 1.5 KB
        ]);

        $formattedSize = $document->formatted_file_size;
        $this->assertStringContainsString('KB', $formattedSize);
        $this->assertEquals('1.5 KB', $formattedSize);

        $document = StudentDocument::factory()->create([
            'file_size' => 1048576, // 1 MB
        ]);

        $formattedSize = $document->formatted_file_size;
        $this->assertStringContainsString('MB', $formattedSize);
        $this->assertEquals('1 MB', $formattedSize);
    }
}
