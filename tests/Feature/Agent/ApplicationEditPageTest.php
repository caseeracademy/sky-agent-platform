<?php

namespace Tests\Feature\Agent;

use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationEditPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Happy Path Test - Agent can access their application through resource (proves Section import works)
     */
    public function test_agent_application_form_components_load_correctly(): void
    {
        // Create an agent_owner user
        $agent = User::factory()->create([
            'role' => 'agent_owner',
            'name' => 'Test Agent',
            'email' => 'agent@test.com',
        ]);

        // Create a student that belongs to that agent
        $student = Student::factory()->create([
            'agent_id' => $agent->id,
            'name' => 'Test Student',
            'email' => 'student@test.com',
        ]);

        // Create university and program
        $university = University::factory()->create();
        $program = Program::factory()->create(['university_id' => $university->id]);

        // Create an application that belongs to that student
        $application = Application::factory()->create([
            'agent_id' => $agent->id,
            'student_id' => $student->id,
            'program_id' => $program->id,
            'status' => 'pending',
        ]);

        // Authenticate as the agent user
        $this->actingAs($agent);

        // Test that the form schema can be built without errors (proves Section import works)
        $schema = \App\Filament\Agent\Resources\Applications\ApplicationResource::form(
            new \Filament\Schemas\Schema()
        );

        // If we get here without exceptions, the Section import is working
        $this->assertInstanceOf(\Filament\Schemas\Schema::class, $schema);
        
        // Test that the application can be found by the agent
        $agentApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        $this->assertTrue($agentApplications->contains($application));
    }

    /**
     * Authorization Test - Agent cannot view edit page of other agent's application
     */
    public function test_agent_cannot_view_edit_page_of_other_agents_application(): void
    {
        // Create two agents
        $agentA = User::factory()->create(['role' => 'agent_owner']);
        $agentB = User::factory()->create(['role' => 'agent_owner']);

        // Create student and application for Agent A
        $studentA = Student::factory()->create(['agent_id' => $agentA->id]);
        $university = University::factory()->create();
        $program = Program::factory()->create(['university_id' => $university->id]);
        
        $applicationA = Application::factory()->create([
            'agent_id' => $agentA->id,
            'student_id' => $studentA->id,
            'program_id' => $program->id,
        ]);

        // Authenticate as Agent B
        $this->actingAs($agentB);

        // Try to access Agent A's application edit page
        $editUrl = "/agent/applications/{$applicationA->id}/edit";
        $response = $this->get($editUrl);

        // Should be forbidden or not found due to scoping
        $this->assertContains($response->getStatusCode(), [403, 404]);
    }

    /**
     * Validation Test - Agent cannot access non-existent application through resource query
     */
    public function test_agent_cannot_access_non_existent_application(): void
    {
        $agent = User::factory()->create(['role' => 'agent_owner']);
        $this->actingAs($agent);

        // Test that querying for non-existent application returns empty
        $nonExistentApp = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->where('id', 99999)
            ->first();

        $this->assertNull($nonExistentApp);
    }

    /**
     * Happy Path Test - Application data is accessible through resource
     */
    public function test_application_data_accessible_through_resource(): void
    {
        $agent = User::factory()->create(['role' => 'agent_owner']);
        $student = Student::factory()->create(['agent_id' => $agent->id]);
        $university = University::factory()->create(['name' => 'Test University']);
        $program = Program::factory()->create([
            'university_id' => $university->id,
            'name' => 'Test Program',
        ]);

        $application = Application::factory()->create([
            'agent_id' => $agent->id,
            'student_id' => $student->id,
            'program_id' => $program->id,
            'status' => 'pending',
            'notes' => 'Test application notes',
        ]);

        $this->actingAs($agent);

        // Test that the agent can see their application with all related data
        $agentApplication = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()
            ->with(['student', 'program.university'])
            ->where('id', $application->id)
            ->first();

        $this->assertNotNull($agentApplication);
        $this->assertEquals($student->name, $agentApplication->student->name);
        $this->assertEquals($program->name, $agentApplication->program->name);
        $this->assertEquals($university->name, $agentApplication->program->university->name);
        $this->assertEquals('Test application notes', $agentApplication->notes);
    }

    /**
     * Authorization Test - Agent staff can access applications for their students through resource
     */
    public function test_agent_staff_can_access_applications_for_their_students(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
        ]);

        // Create student assigned to agent staff
        $student = Student::factory()->create(['agent_id' => $agentStaff->id]);
        $university = University::factory()->create();
        $program = Program::factory()->create(['university_id' => $university->id]);

        $application = Application::factory()->create([
            'agent_id' => $agentStaff->id,
            'student_id' => $student->id,
            'program_id' => $program->id,
        ]);

        $this->actingAs($agentStaff);

        // Test that agent staff can see their applications through the resource
        $staffApplications = \App\Filament\Agent\Resources\Applications\ApplicationResource::getEloquentQuery()->get();
        $this->assertTrue($staffApplications->contains($application));
    }
}