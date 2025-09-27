<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $agentOwner;
    protected User $agentStaff;
    protected Student $student;
    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $this->agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $this->agentOwner->id,
        ]);

        // Create test data
        $university = University::factory()->create();
        $this->program = Program::factory()->create(['university_id' => $university->id]);
        $this->student = Student::factory()->create(['agent_id' => $this->agentOwner->id]);
    }

    /**
     * Authorization Test - Agent cannot access admin application resource
     */
    public function test_agent_owner_cannot_access_admin_application_resource(): void
    {
        $this->actingAs($this->agentOwner);

        // Try to access admin applications endpoint
        $response = $this->get('/admin/applications');

        $response->assertStatus(403);
    }

    /**
     * Authorization Test - Agent staff cannot access admin application resource
     */
    public function test_agent_staff_cannot_access_admin_application_resource(): void
    {
        $this->actingAs($this->agentStaff);

        // Try to access admin applications endpoint
        $response = $this->get('/admin/applications');

        $response->assertStatus(403);
    }

    /**
     * Happy Path Test - Super admin can see all applications
     */
    public function test_super_admin_can_see_all_applications(): void
    {
        // Create applications from different agents
        $application1 = Application::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
        ]);

        $otherAgent = User::factory()->create(['role' => 'agent_owner']);
        $otherStudent = Student::factory()->create(['agent_id' => $otherAgent->id]);
        $application2 = Application::factory()->create([
            'agent_id' => $otherAgent->id,
            'student_id' => $otherStudent->id,
            'program_id' => $this->program->id,
        ]);

        $this->actingAs($this->superAdmin);

        // Test that admin can see all applications
        $allApplications = \App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()->get();

        $this->assertTrue($allApplications->contains($application1));
        $this->assertTrue($allApplications->contains($application2));
        $this->assertCount(2, $allApplications);
    }

    /**
     * Data Scoping Test - Agents can only see their own applications
     */
    public function test_agents_can_only_see_their_own_applications(): void
    {
        // Create applications for different agents
        $application1 = Application::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
        ]);

        $otherAgent = User::factory()->create(['role' => 'agent_owner']);
        $otherStudent = Student::factory()->create(['agent_id' => $otherAgent->id]);
        $application2 = Application::factory()->create([
            'agent_id' => $otherAgent->id,
            'student_id' => $otherStudent->id,
            'program_id' => $this->program->id,
        ]);

        // Login as first agent
        $this->actingAs($this->agentOwner);

        // Test agent portal application scoping
        $agentApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();

        // Should only see their own application
        $this->assertTrue($agentApplications->contains($application1));
        $this->assertFalse($agentApplications->contains($application2));
        $this->assertCount(1, $agentApplications);
    }

    /**
     * Validation Test - Application requires valid relationships
     */
    public function test_application_requires_valid_student_and_program(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Application::create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => 99999, // Non-existent student
            'program_id' => $this->program->id,
        ]);
    }

    /**
     * Happy Path Test - Application creation works with valid data
     */
    public function test_application_creation_works_with_valid_data(): void
    {
        $application = Application::create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'status' => 'pending',
            'notes' => 'Test application',
        ]);

        $this->assertDatabaseHas('applications', [
            'agent_id' => $this->agentOwner->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'status' => 'pending',
        ]);

        // Test relationships work
        $this->assertEquals($this->student->id, $application->student->id);
        $this->assertEquals($this->program->id, $application->program->id);
        $this->assertEquals($this->agentOwner->id, $application->agent->id);

        // Test application number was auto-generated
        $this->assertNotNull($application->application_number);
        $this->assertStringStartsWith('APP-' . now()->format('Y'), $application->application_number);
    }

    /**
     * Happy Path Test - Commission calculation works
     */
    public function test_commission_calculation_works(): void
    {
        $application = Application::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
        ]);

        $application->calculateCommission();

        $this->assertEquals($this->program->agent_commission, $application->commission_amount);
    }

    /**
     * Happy Path Test - Application status transitions work
     */
    public function test_application_status_transitions_work(): void
    {
        $application = Application::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'status' => 'pending',
        ]);

        // Test submission
        $this->assertTrue($application->canBeSubmitted());
        $application->markAsSubmitted();

        $application->refresh();
        $this->assertEquals('submitted', $application->status);
        $this->assertNotNull($application->submitted_at);
        $this->assertFalse($application->canBeSubmitted());
    }
}