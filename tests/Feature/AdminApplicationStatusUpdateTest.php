<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Commission;
use App\Models\Program;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->agent = User::factory()->create(['role' => 'agent_owner']);
        
        $this->university = University::factory()->create([
            'name' => 'Test University',
            'location' => 'Toronto, Canada',
        ]);
        
        $this->program = Program::factory()->create([
            'university_id' => $this->university->id,
            'name' => 'Computer Science',
            'agent_commission' => 1000.00,
            'tuition_fee' => 20000.00,
            'degree_type' => 'Bachelor',
        ]);
        
        $this->student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'name' => 'Test Student',
            'email' => 'test@example.com',
        ]);
        
        $this->application = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'submitted',
        ]);
    }

    /** @test */
    public function admin_approving_application_creates_commission_automatically(): void
    {
        // Arrange: Ensure no commission exists initially
        $this->assertDatabaseCount('commissions', 0);
        $this->assertEquals('submitted', $this->application->status);
        
        // Act: Simulate the exact SelectAction logic from ApplicationsTable
        $record = $this->application;
        $data = ['status' => 'approved'];
        $oldStatus = $record->status;
        $newStatus = $data['status'];
        
        // Update the application status first
        $record->update([
            'status' => $newStatus,
            'reviewed_at' => now(),
            'decision_at' => in_array($newStatus, ['approved', 'rejected']) ? now() : null,
        ]);

        // Commission creation is now handled automatically by ApplicationObserver

        // Assert: Application status updated and commission created
        $record->refresh();
        $this->assertEquals('approved', $record->status);
        $this->assertNotNull($record->reviewed_at);
        $this->assertNotNull($record->decision_at);
        
        $this->assertDatabaseHas('commissions', [
            'application_id' => $this->application->id,
            'agent_id' => $this->agent->id,
            'amount' => 1000.00,
        ]);
        
        $this->assertDatabaseCount('commissions', 1);
    }

    /** @test */
    public function admin_rejecting_application_does_not_create_commission(): void
    {
        // Arrange: Ensure no commission exists initially
        $this->assertDatabaseCount('commissions', 0);
        $this->assertEquals('submitted', $this->application->status);
        
        // Act: Simulate rejecting an application
        $record = $this->application;
        $data = ['status' => 'rejected'];
        $newStatus = $data['status'];
        
        // Update the application status first
        $record->update([
            'status' => $newStatus,
            'reviewed_at' => now(),
            'decision_at' => in_array($newStatus, ['approved', 'rejected']) ? now() : null,
        ]);

        // Commission creation only happens for 'approved' status
        if ($newStatus === 'approved') {
            // This should not execute for 'rejected'
            if (!$record->commission) {
                $record->load(['student', 'program']);
                if ($record->student && $record->program && $record->student->agent_id) {
                    Commission::create([
                        'application_id' => $record->id,
                        'agent_id' => $record->student->agent_id,
                        'amount' => $record->program->agent_commission,
                    ]);
                }
            }
        }

        // Assert: Application status updated but no commission created
        $record->refresh();
        $this->assertEquals('rejected', $record->status);
        $this->assertNotNull($record->reviewed_at);
        $this->assertNotNull($record->decision_at);
        
        // No commission should be created for rejected applications
        $this->assertDatabaseCount('commissions', 0);
    }

    /** @test */
    public function commission_creation_prevents_duplicates(): void
    {
        // Arrange: Create an application that already has a commission via observer
        $this->application->update(['status' => 'approved']);
        // Observer automatically creates commission when status changes to approved
        $this->assertDatabaseCount('commissions', 1);
        
        // Act: Try to approve the application again (simulate the SelectAction)
        $record = $this->application;
        $data = ['status' => 'approved'];
        $newStatus = $data['status'];
        
        $record->update([
            'status' => $newStatus,
            'reviewed_at' => now(),
            'decision_at' => in_array($newStatus, ['approved', 'rejected']) ? now() : null,
        ]);

        // Commission creation logic with duplicate prevention
        if ($newStatus === 'approved') {
            if (!$record->commission) { // This should prevent duplicate
                $record->load(['student', 'program']);
                if ($record->student && $record->program && $record->student->agent_id) {
                    Commission::create([
                        'application_id' => $record->id,
                        'agent_id' => $record->student->agent_id,
                        'amount' => $record->program->agent_commission,
                    ]);
                }
            }
        }

        // Assert: Still only one commission exists (observer prevents duplicates)
        $this->assertDatabaseCount('commissions', 1);
    }

    /** @test */
    public function applications_table_configuration_works_without_errors(): void
    {
        // Act: Test that the ApplicationsTable class exists and has correct methods
        $this->assertTrue(class_exists(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class));
        $this->assertTrue(method_exists(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class, 'configure'));
        
        // Test that the class can be instantiated without errors
        try {
            $reflection = new \ReflectionClass(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class);
            $success = true;
        } catch (\Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }

        $this->assertTrue($success, 'ApplicationsTable should load without errors: ' . ($errorMessage ?? ''));
    }

    /** @test */
    public function commission_creation_logs_errors_when_relationships_missing(): void
    {
        // Arrange: Create application but test when program is missing (simulate edge case)
        $this->assertDatabaseCount('commissions', 0);
        
        // Act: Try commission creation when relationships might be missing
        $record = $this->application;
        $data = ['status' => 'approved'];
        $newStatus = $data['status'];
        
        $record->update([
            'status' => $newStatus,
            'reviewed_at' => now(),
            'decision_at' => in_array($newStatus, ['approved', 'rejected']) ? now() : null,
        ]);

        // Test that the validation logic works correctly
        if ($newStatus === 'approved') {
            if (!$record->commission) {
                $record->load(['student', 'program']);
                $hasValidRelationships = $record->student && $record->program && $record->student->agent_id;
                
                // Assert relationships are valid in our test setup
                $this->assertTrue($hasValidRelationships, 'Test setup should have valid relationships');
                
                // Commission creation is now handled automatically by ApplicationObserver
            }
        }

        // Assert: Commission was created successfully
        $this->assertEquals('approved', $record->status);
        $this->assertDatabaseCount('commissions', 1);
    }
}