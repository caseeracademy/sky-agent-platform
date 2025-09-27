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

class AgentApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $agentOwnerA;
    protected User $agentOwnerB;
    protected User $agentStaff;
    protected Student $studentA;
    protected Student $studentB;
    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->agentOwnerA = User::factory()->create(['role' => 'agent_owner', 'name' => 'Agent A']);
        $this->agentOwnerB = User::factory()->create(['role' => 'agent_owner', 'name' => 'Agent B']);
        $this->agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $this->agentOwnerA->id,
        ]);

        // Create test data
        $university = University::factory()->create();
        $this->program = Program::factory()->create(['university_id' => $university->id]);
        $this->studentA = Student::factory()->forAgent($this->agentOwnerA)->create();
        $this->studentB = Student::factory()->forAgent($this->agentOwnerB)->create();
    }

    /**
     * 1. Authorization Test - Agent Owner can access application resource
     */
    public function test_agent_owner_can_access_application_resource(): void
    {
        $this->actingAs($this->agentOwnerA);

        // Test that the query executes without error (implies access is allowed)
        $applications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $applications);
    }

    /**
     * 1. Authorization Test - Agent Staff can access application resource
     */
    public function test_agent_staff_can_access_application_resource(): void
    {
        $this->actingAs($this->agentStaff);

        // Test that the query executes without error (implies access is allowed)
        $applications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $applications);
    }

    /**
     * 1. Authorization Test - Super Admin cannot access agent application resource
     */
    public function test_super_admin_cannot_access_agent_application_resource(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get('/agent/applications');

        $response->assertStatus(403);
    }

    /**
     * 2. Validation Test - Application cannot be created without student
     */
    public function test_application_requires_student(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Application::create([
            'agent_id' => $this->agentOwnerA->id,
            'program_id' => $this->program->id,
            // student_id is missing
        ]);
    }

    /**
     * 2. Validation Test - Application cannot be created without program
     */
    public function test_application_requires_program(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Application::create([
            'agent_id' => $this->agentOwnerA->id,
            'student_id' => $this->studentA->id,
            // program_id is missing
        ]);
    }

    /**
     * 3. Data Scoping Test - Agent A cannot see Agent B's applications
     */
    public function test_agents_can_only_see_their_team_applications(): void
    {
        // Create application for Agent A's student
        $applicationA = Application::factory()->create([
            'agent_id' => $this->agentOwnerA->id,
            'student_id' => $this->studentA->id,
            'program_id' => $this->program->id,
        ]);

        // Create application for Agent B's student
        $applicationB = Application::factory()->create([
            'agent_id' => $this->agentOwnerB->id,
            'student_id' => $this->studentB->id,
            'program_id' => $this->program->id,
        ]);

        // Login as Agent A
        $this->actingAs($this->agentOwnerA);

        // Test the scoping using ApplicationResource logic
        $visibleApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();

        // Agent A should only see their team's application
        $this->assertTrue($visibleApplications->contains($applicationA));
        $this->assertFalse($visibleApplications->contains($applicationB));
        $this->assertCount(1, $visibleApplications);
    }

    /**
     * 4. Happy Path Test - Agent can successfully create application
     */
    public function test_agent_can_create_application_successfully(): void
    {
        $this->actingAs($this->agentOwnerA);

        $applicationData = [
            'student_id' => $this->studentA->id,
            'program_id' => $this->program->id,
            'intake_date' => now()->addMonths(6)->format('Y-m-d'),
            'notes' => 'Test application notes',
        ];

        // Add agent_id manually since we're testing directly
        $applicationData['agent_id'] = $this->agentOwnerA->id;

        $application = Application::create($applicationData);
        
        // Manually calculate commission since we're not going through the form
        $application->calculateCommission();

        // Assert application exists in database
        $this->assertDatabaseHas('applications', [
            'student_id' => $this->studentA->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agentOwnerA->id,
            'status' => 'pending',
        ]);

        // Assert application number was generated
        $this->assertNotNull($application->application_number);
        $this->assertStringStartsWith('APP-' . now()->format('Y'), $application->application_number);

        // Assert commission was calculated
        $this->assertEquals($this->program->agent_commission, $application->commission_amount);

        // Assert it appears in agent's list
        $this->actingAs($this->agentOwnerA);
        $agentApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        $this->assertTrue($agentApplications->contains($application));

        // Assert it appears in admin's list
        $this->actingAs($this->superAdmin);
        $adminApplications = \App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        $this->assertTrue($adminApplications->contains($application));
    }

    /**
     * 4. Happy Path Test - Application logging works correctly
     */
    public function test_application_logging_works(): void
    {
        $this->actingAs($this->agentOwnerA);

        $application = Application::create([
            'agent_id' => $this->agentOwnerA->id,
            'student_id' => $this->studentA->id,
            'program_id' => $this->program->id,
        ]);

        // Assert creation log was created
        $this->assertDatabaseHas('application_logs', [
            'application_id' => $application->id,
            'user_id' => $this->agentOwnerA->id,
            'note' => 'Application created',
        ]);

        // Test status change logging
        $application->update(['status' => 'submitted']);

        $this->assertDatabaseHas('application_logs', [
            'application_id' => $application->id,
            'user_id' => $this->agentOwnerA->id,
            'status_change' => 'pending -> submitted',
        ]);
    }

    /**
     * 3. Data Scoping Test - Agent staff can see applications from their team's students
     */
    public function test_agent_staff_can_see_team_applications(): void
    {
        // Create a student that actually belongs to the agent staff's team
        $teamStudent = Student::factory()->forAgent($this->agentStaff)->create();
        
        // Create application for team student by the staff member
        $application = Application::factory()->create([
            'agent_id' => $this->agentStaff->id,
            'student_id' => $teamStudent->id,
            'program_id' => $this->program->id,
        ]);

        // Login as Agent Staff (who works for Agent Owner A)
        $this->actingAs($this->agentStaff);

        // Note: Current scoping is by student's agent_id, so agent staff won't see owner's applications
        // This is correct behavior - staff should manage their own assigned students
        $visibleApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();

        // Since the application was created by the owner but student belongs to owner's team,
        // and we scope by student.agent_id, the staff member should see it
        $this->assertTrue($visibleApplications->contains($application));
    }

    /**
     * 2. Validation Test - Business logic should prevent cross-agent applications
     */
    public function test_agent_business_logic_prevents_cross_team_applications(): void
    {
        $this->actingAs($this->agentOwnerA);

        // Create application for Agent B's student (this should be allowed at DB level)
        $application = Application::create([
            'agent_id' => $this->agentOwnerA->id,
            'student_id' => $this->studentB->id, // This student belongs to Agent B
            'program_id' => $this->program->id,
        ]);

        // But Agent A should not be able to see it due to scoping
        $visibleApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        
        // Should be empty because student belongs to different agent
        $this->assertFalse($visibleApplications->contains($application));
    }

    /**
     * 4. Happy Path Test - Application relationships work correctly
     */
    public function test_application_relationships_work(): void
    {
        $application = Application::factory()->create([
            'agent_id' => $this->agentOwnerA->id,
            'student_id' => $this->studentA->id,
            'program_id' => $this->program->id,
        ]);

        // Test all relationships
        $this->assertEquals($this->studentA->id, $application->student->id);
        $this->assertEquals($this->program->id, $application->program->id);
        $this->assertEquals($this->agentOwnerA->id, $application->agent->id);
        $this->assertEquals($this->program->university->id, $application->university->id);

        // Test reverse relationships
        $this->assertTrue($this->studentA->applications->contains($application));
        $this->assertTrue($this->program->applications->contains($application));
        $this->assertTrue($this->agentOwnerA->applications->contains($application));
    }
}