<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Program;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminStaff;
    protected User $agentOwner;
    protected Application $application;
    protected ApplicationDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->adminStaff = User::factory()->create(['role' => 'admin_staff']);
        $this->agentOwner = User::factory()->create(['role' => 'agent_owner']);

        // Create application with document
        $university = University::factory()->create();
        $program = Program::factory()->create(['university_id' => $university->id]);
        $student = Student::factory()->forAgent($this->agentOwner)->create();

        $this->application = Application::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => $student->id,
            'program_id' => $program->id,
        ]);

        // Create a document
        Storage::fake('public');
        $file = UploadedFile::fake()->create('transcript.pdf', 1024, 'application/pdf');
        $path = $file->store('application-documents', 'public');

        $this->document = ApplicationDocument::create([
            'application_id' => $this->application->id,
            'uploaded_by_user_id' => $this->agentOwner->id,
            'original_filename' => 'transcript.pdf',
            'disk' => 'public',
            'path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    /**
     * Happy Path Test - Super Admin can see all applications with documents
     */
    public function test_super_admin_can_see_all_applications_with_documents(): void
    {
        $this->actingAs($this->superAdmin);

        // Get applications through admin resource
        $adminApplications = \App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->with('applicationDocuments')
            ->get();

        // Super Admin should see all applications
        $this->assertTrue($adminApplications->contains($this->application));

        // Should see the application's documents
        $applicationWithDocs = $adminApplications->where('id', $this->application->id)->first();
        $this->assertTrue($applicationWithDocs->applicationDocuments->contains($this->document));
    }

    /**
     * Happy Path Test - Admin Staff can see all applications with documents
     */
    public function test_admin_staff_can_see_all_applications_with_documents(): void
    {
        $this->actingAs($this->adminStaff);

        // Get applications through admin resource
        $adminApplications = \App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->with('applicationDocuments')
            ->get();

        // Admin Staff should see all applications
        $this->assertTrue($adminApplications->contains($this->application));

        // Should see the application's documents
        $applicationWithDocs = $adminApplications->where('id', $this->application->id)->first();
        $this->assertTrue($applicationWithDocs->applicationDocuments->contains($this->document));
    }

    /**
     * Happy Path Test - Document download URL generation works
     */
    public function test_document_download_url_generation_works(): void
    {
        $downloadUrl = $this->document->download_url;

        // Should generate proper storage URL
        $this->assertStringContainsString('storage/application-documents', $downloadUrl);
        // Note: Laravel generates unique filenames, so we test the path contains our directory
    }

    /**
     * Happy Path Test - Document metadata is properly displayed
     */
    public function test_document_metadata_is_properly_displayed(): void
    {
        // Test file size formatting
        $formattedSize = $this->document->formatted_file_size;
        $this->assertStringContainsString('KB', $formattedSize);

        // Test relationships
        $this->assertEquals($this->application->id, $this->document->application->id);
        $this->assertEquals($this->agentOwner->id, $this->document->uploadedByUser->id);
        $this->assertEquals('transcript.pdf', $this->document->original_filename);
    }

    /**
     * Authorization Test - Agents cannot access admin document interface
     */
    public function test_agents_cannot_access_admin_document_interface(): void
    {
        $this->actingAs($this->agentOwner);

        // Try to access admin applications (which shows documents)
        $response = $this->get('/admin/applications');

        $response->assertStatus(403);
    }

    /**
     * Data Scoping Test - Admin sees documents from all agents
     */
    public function test_admin_sees_documents_from_all_agents(): void
    {
        // Create another agent and application with document
        $otherAgent = User::factory()->create(['role' => 'agent_owner']);
        $otherStudent = Student::factory()->forAgent($otherAgent)->create();
        $university = University::factory()->create();
        $program = Program::factory()->create(['university_id' => $university->id]);

        $otherApplication = Application::factory()->create([
            'agent_id' => $otherAgent->id,
            'student_id' => $otherStudent->id,
            'program_id' => $program->id,
        ]);

        $otherDocument = ApplicationDocument::create([
            'application_id' => $otherApplication->id,
            'uploaded_by_user_id' => $otherAgent->id,
            'original_filename' => 'diploma.pdf',
            'disk' => 'public',
            'path' => 'application-documents/diploma.pdf',
            'file_size' => 1024 * 1024,
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($this->superAdmin);

        // Admin should see both applications and their documents
        $adminApplications = \App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->with('applicationDocuments')
            ->get();

        $this->assertTrue($adminApplications->contains($this->application));
        $this->assertTrue($adminApplications->contains($otherApplication));

        // Should see documents from both agents by checking document counts
        $totalDocuments = $adminApplications->sum(function ($app) {
            return $app->applicationDocuments->count();
        });
        
        $this->assertEquals(2, $totalDocuments); // Should see both documents
        
        // Verify specific applications have their documents
        $app1 = $adminApplications->where('id', $this->application->id)->first();
        $app2 = $adminApplications->where('id', $otherApplication->id)->first();
        
        $this->assertEquals(1, $app1->applicationDocuments->count());
        $this->assertEquals(1, $app2->applicationDocuments->count());
    }

    /**
     * Validation Test - Document relationships are properly constrained
     */
    public function test_document_relationships_are_properly_constrained(): void
    {
        // Test that document belongs to application
        $this->assertEquals($this->application->id, $this->document->application_id);

        // Test that deleting application cascades to documents
        $documentId = $this->document->id;
        $this->application->delete();

        $this->assertDatabaseMissing('application_documents', ['id' => $documentId]);
    }
}
