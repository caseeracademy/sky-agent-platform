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

class DocumentUploadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $agentA;
    protected User $agentB;
    protected User $superAdmin;
    protected Application $applicationA;
    protected Application $applicationB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agentA = User::factory()->create(['role' => 'agent_owner']);
        $this->agentB = User::factory()->create(['role' => 'agent_owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        $university = University::factory()->create();
        $program = Program::factory()->create(['university_id' => $university->id]);
        
        $studentA = Student::factory()->forAgent($this->agentA)->create();
        $studentB = Student::factory()->forAgent($this->agentB)->create();

        $this->applicationA = Application::factory()->create([
            'agent_id' => $this->agentA->id,
            'student_id' => $studentA->id,
            'program_id' => $program->id,
        ]);

        $this->applicationB = Application::factory()->create([
            'agent_id' => $this->agentB->id,
            'student_id' => $studentB->id,
            'program_id' => $program->id,
        ]);

        Storage::fake('public');
    }

    public function test_agent_can_upload_document_successfully(): void
    {
        $this->actingAs($this->agentA);

        $file = UploadedFile::fake()->create('transcript.pdf', 2048, 'application/pdf');
        $path = $file->store('application-documents', 'public');

        $document = ApplicationDocument::create([
            'application_id' => $this->applicationA->id,
            'uploaded_by_user_id' => $this->agentA->id,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        $this->assertDatabaseHas('application_documents', [
            'application_id' => $this->applicationA->id,
            'uploaded_by_user_id' => $this->agentA->id,
            'original_filename' => 'transcript.pdf',
        ]);

        Storage::disk('public')->assertExists($path);
        $this->assertEquals($this->applicationA->id, $document->application->id);
    }

    public function test_document_deletion_removes_file(): void
    {
        $this->actingAs($this->agentA);

        $file = UploadedFile::fake()->create('temp.pdf', 1024, 'application/pdf');
        $path = $file->store('application-documents', 'public');

        $document = ApplicationDocument::create([
            'application_id' => $this->applicationA->id,
            'uploaded_by_user_id' => $this->agentA->id,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        Storage::disk('public')->assertExists($path);
        $document->delete();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_agents_cannot_see_other_agents_documents(): void
    {
        $this->actingAs($this->agentA);
        $file = UploadedFile::fake()->create('secret.pdf', 1024, 'application/pdf');
        
        $document = ApplicationDocument::create([
            'application_id' => $this->applicationA->id,
            'uploaded_by_user_id' => $this->agentA->id,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $file->store('application-documents', 'public'),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        $this->actingAs($this->agentB);
        $agentBApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->with('applicationDocuments')
            ->get();

        $this->assertFalse($agentBApplications->contains($this->applicationA));
    }

    public function test_super_admin_can_see_all_documents(): void
    {
        $this->actingAs($this->agentA);
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');
        
        $document = ApplicationDocument::create([
            'application_id' => $this->applicationA->id,
            'uploaded_by_user_id' => $this->agentA->id,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $file->store('application-documents', 'public'),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        $this->actingAs($this->superAdmin);
        $adminApplications = \App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->with('applicationDocuments')
            ->get();

        $this->assertTrue($adminApplications->contains($this->applicationA));
        
        $adminApplication = $adminApplications->where('id', $this->applicationA->id)->first();
        $this->assertTrue($adminApplication->applicationDocuments->contains($document));
    }
}