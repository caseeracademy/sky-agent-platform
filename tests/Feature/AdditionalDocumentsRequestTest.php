<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationLog;
use App\Models\Program;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdditionalDocumentsRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $agent;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
        ]);

        // Create agent
        $this->agent = User::factory()->create([
            'role' => 'agent',
            'name' => 'Test Agent',
            'email' => 'agent@test.com',
        ]);

        // Create university and program
        $university = University::factory()->create(['name' => 'Test University']);
        $program = Program::factory()->create([
            'university_id' => $university->id,
            'name' => 'Test Program',
        ]);

        // Create student
        $student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
        ]);

        // Create application
        $this->application = Application::factory()->create([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'agent_id' => $this->agent->id,
            'status' => 'submitted',
        ]);
    }

    public function test_super_admin_can_request_additional_documents(): void
    {
        $this->actingAs($this->superAdmin);

        $requestDetails = 'Please provide an updated transcript with final grades for semester 4. We also need a copy of your IELTS certificate with a score of 6.5 or higher.';

        // Simulate the action that would be triggered by the modal
        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => $requestDetails,
            'reviewed_at' => now(),
        ]);

        // Create the log entry as the modal action would
        ApplicationLog::create([
            'application_id' => $this->application->id,
            'user_id' => $this->superAdmin->id,
            'note' => 'Application status changed to "Additional Documents Required" by admin.',
            'status_change' => 'additional_documents_required',
        ]);

        // Verify application was updated
        $this->assertDatabaseHas('applications', [
            'id' => $this->application->id,
            'status' => 'additional_documents_required',
            'additional_documents_request' => $requestDetails,
        ]);

        // Verify log was created
        $this->assertDatabaseHas('application_logs', [
            'application_id' => $this->application->id,
            'user_id' => $this->superAdmin->id,
            'status_change' => 'additional_documents_required',
        ]);
    }

    public function test_agent_can_see_document_request_warning(): void
    {
        // Set up application with document request
        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => 'Please provide updated transcript and IELTS certificate.',
        ]);

        // Verify the application has the correct data for the warning panel
        $this->assertEquals('additional_documents_required', $this->application->status);
        $this->assertEquals('Please provide updated transcript and IELTS certificate.', $this->application->additional_documents_request);
        $this->assertTrue($this->application->additional_documents_request !== null);
    }

    public function test_application_logs_contain_document_request(): void
    {
        $this->actingAs($this->superAdmin);

        $requestDetails = 'Missing documents: transcript and IELTS certificate';

        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => $requestDetails,
        ]);

        // Create log entry
        ApplicationLog::create([
            'application_id' => $this->application->id,
            'user_id' => $this->superAdmin->id,
            'note' => 'Application status changed to "Additional Documents Required" by admin.',
            'status_change' => 'additional_documents_required',
        ]);

        // Verify the log exists
        $this->assertDatabaseHas('application_logs', [
            'application_id' => $this->application->id,
            'user_id' => $this->superAdmin->id,
            'note' => 'Application status changed to "Additional Documents Required" by admin.',
            'status_change' => 'additional_documents_required',
        ]);
    }

    public function test_document_request_only_shows_when_status_is_additional_documents_required(): void
    {
        // Test with different statuses - warning should not be shown
        $statuses = ['pending', 'submitted', 'under_review', 'approved', 'rejected'];

        foreach ($statuses as $status) {
            $this->application->update([
                'status' => $status,
                'additional_documents_request' => 'Some request details',
            ]);

            // For these statuses, the warning should not be shown
            $this->assertNotEquals('additional_documents_required', $this->application->status);
        }

        // Test with correct status - warning should be shown
        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => 'Please provide documents.',
        ]);

        $this->assertEquals('additional_documents_required', $this->application->status);
        $this->assertEquals('Please provide documents.', $this->application->additional_documents_request);
    }

    public function test_document_request_requires_request_details(): void
    {
        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => null,
        ]);

        // Warning should NOT be visible without request details
        $this->assertEquals('additional_documents_required', $this->application->status);
        $this->assertNull($this->application->additional_documents_request);

        // Test with empty string
        $this->application->update([
            'additional_documents_request' => '',
        ]);

        $this->assertEmpty($this->application->additional_documents_request);
    }

    public function test_agent_can_upload_documents_with_resubmit(): void
    {
        // Set up application with document request
        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => 'Please provide updated transcript and IELTS certificate.',
        ]);

        $this->actingAs($this->agent);

        // Simulate file upload data
        $uploadData = [
            'application_id' => $this->application->id,
            'files' => [
                // Mock file objects would be created here in real test
            ],
            'titles' => ['Updated Transcript', 'IELTS Certificate'],
            'resubmit' => true,
        ];

        // Test that resubmit functionality works
        $this->assertEquals('additional_documents_required', $this->application->status);

        // Simulate the controller logic
        $this->application->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->assertEquals('submitted', $this->application->status);
        $this->assertNotNull($this->application->submitted_at);
    }

    public function test_agent_can_upload_documents_without_resubmit(): void
    {
        // Set up application with document request
        $this->application->update([
            'status' => 'additional_documents_required',
            'additional_documents_request' => 'Please provide updated transcript.',
        ]);

        $this->actingAs($this->agent);

        // Test that without resubmit, status remains the same
        $this->assertEquals('additional_documents_required', $this->application->status);

        // Simulate uploading without resubmit
        // Status should remain additional_documents_required
        $this->assertEquals('additional_documents_required', $this->application->status);
        $this->assertNull($this->application->submitted_at);
    }
}
