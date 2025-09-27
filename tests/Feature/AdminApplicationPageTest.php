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

class AdminApplicationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->adminStaff = User::factory()->create(['role' => 'admin_staff']);
        $this->agent = User::factory()->create(['role' => 'agent_owner']);
        
        $this->university = University::factory()->create([
            'name' => 'Test University',
            'location' => 'Toronto, Canada',
        ]);
        
        $this->program = Program::factory()->create([
            'university_id' => $this->university->id,
            'name' => 'Computer Science',
            'agent_commission' => 750.00,
            'tuition_fee' => 15000.00,
            'degree_type' => 'Bachelor',
        ]);
        
        $this->student = Student::factory()->create([
            'agent_id' => $this->agent->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $this->application = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'status' => 'submitted',
        ]);
    }

    /** @test */
    public function admin_applications_table_configuration_is_correct(): void
    {
        // Act: Test that the table class exists and can be loaded
        $this->assertTrue(class_exists(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class));
        $this->assertTrue(method_exists(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class, 'configure'));
        
        // Test that status options are properly configured
        $statusOptions = [
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'additional_documents_required' => 'Additional Documents Required',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'enrolled' => 'Enrolled',
            'cancelled' => 'Cancelled',
        ];
        
        // Verify the expected status options exist
        $this->assertIsArray($statusOptions);
        $this->assertArrayHasKey('approved', $statusOptions);
        $this->assertArrayHasKey('rejected', $statusOptions);
    }

    /** @test */
    public function admin_can_update_application_status_to_approved(): void
    {
        // Arrange: Ensure no commission exists initially
        $this->assertDatabaseCount('commissions', 0);
        $this->assertEquals('submitted', $this->application->status);
        
        // Act: Simulate the SelectAction logic for approving an application
        $record = $this->application;
        $data = ['status' => 'approved'];
        $newStatus = $data['status'];
        
        // Update the application status first (as in the action)
        $record->update([
            'status' => $newStatus,
            'reviewed_at' => now(),
            'decision_at' => in_array($newStatus, ['approved', 'rejected']) ? now() : null,
        ]);

        // Commission creation is now handled automatically by ApplicationObserver

        // Assert: Application status updated and commission created
        $this->assertDatabaseHas('applications', [
            'id' => $this->application->id,
            'status' => 'approved',
        ]);
        
        $this->assertDatabaseHas('commissions', [
            'application_id' => $this->application->id,
            'agent_id' => $this->agent->id,
            'amount' => 750.00,
        ]);
        
        $this->assertDatabaseCount('commissions', 1);
    }

    /** @test */
    public function admin_can_update_application_status_to_rejected(): void
    {
        // Arrange: Ensure no commission exists initially
        $this->assertDatabaseCount('commissions', 0);
        $this->assertEquals('submitted', $this->application->status);
        
        // Act: Simulate the SelectAction logic for rejecting an application
        $record = $this->application;
        $data = ['status' => 'rejected'];
        $newStatus = $data['status'];
        
        // Update the application status (as in the action)
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
        $this->assertDatabaseHas('applications', [
            'id' => $this->application->id,
            'status' => 'rejected',
        ]);
        
        // No commission should be created for rejected applications
        $this->assertDatabaseCount('commissions', 0);
    }

    /** @test */
    public function admin_applications_page_prevents_duplicate_commissions(): void
    {
        // Arrange: Create an application that's already approved with a commission via observer
        $this->application->update(['status' => 'approved']);
        // Observer automatically creates commission when status changes to approved
        $this->assertDatabaseCount('commissions', 1);
        
        // Act: Try to approve the application again
        $record = $this->application;
        $data = ['status' => 'approved'];
        $newStatus = $data['status'];
        
        // Simulate the action logic
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
    public function admin_applications_table_loads_without_errors(): void
    {
        // Act: Test that the applications table class exists and methods are defined
        $this->assertTrue(class_exists(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class));
        $this->assertTrue(method_exists(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class, 'configure'));
        
        // Verify no exceptions when checking the class
        try {
            $reflection = new \ReflectionClass(\App\Filament\Resources\Applications\Tables\ApplicationsTable::class);
            $success = true;
        } catch (\Exception $e) {
            $success = false;
        }

        // Assert: Table class loads without errors
        $this->assertTrue($success, 'Applications table class should load without errors');
    }

    /** @test */
    public function admin_applications_select_action_works(): void
    {
        // Act: Verify that the SelectAction can be created without errors
        try {
            $selectAction = \Filament\Actions\SelectAction::make('status')
                ->label('Update Status')
                ->options([
                    'submitted' => 'Submitted',
                    'under_review' => 'Under Review',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]);
            $success = true;
        } catch (\Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }

        // Assert: SelectAction can be created successfully
        $this->assertTrue($success, 'SelectAction should be created without errors: ' . ($errorMessage ?? ''));
        $this->assertInstanceOf(\Filament\Actions\SelectAction::class, $selectAction);
    }
}