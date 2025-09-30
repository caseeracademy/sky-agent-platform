<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentApplicationDeleteRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected Student $student;

    protected Program $program;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->agent = User::factory()->create(['role' => 'agent_owner']);
        $this->student = Student::factory()->create(['agent_id' => $this->agent->id]);
        $this->program = Program::factory()->create();
        $this->application = Application::factory()->create([
            'agent_id' => $this->agent->id,
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
        ]);
    }

    /**
     * Test that agents cannot delete applications via bulk action
     */
    public function test_agents_cannot_bulk_delete_applications(): void
    {
        $this->actingAs($this->agent);

        // Try to delete application via bulk action
        $response = $this->deleteJson('/agent/applications', [
            'action' => 'bulk-delete',
            'ids' => [$this->application->id],
        ]);

        // Should be forbidden or not found
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 404 ||
            $response->status() === 405 // Method not allowed
        );

        // Application should still exist
        $this->assertDatabaseHas('applications', [
            'id' => $this->application->id,
        ]);
    }

    /**
     * Test that agents cannot delete applications via individual delete action
     */
    public function test_agents_cannot_delete_individual_application(): void
    {
        $this->actingAs($this->agent);

        // Try to delete application via individual delete action
        $response = $this->deleteJson("/agent/applications/{$this->application->id}");

        // Should be forbidden or not found
        $this->assertTrue(
            $response->status() === 403 ||
            $response->status() === 404 ||
            $response->status() === 405 // Method not allowed
        );

        // Application should still exist
        $this->assertDatabaseHas('applications', [
            'id' => $this->application->id,
        ]);
    }

    /**
     * Test that applications are preserved when agents try to delete them
     */
    public function test_applications_are_preserved_when_agents_try_to_delete(): void
    {
        $this->actingAs($this->agent);

        // Try multiple deletion methods
        $methods = [
            'bulk' => ['method' => 'delete', 'url' => '/agent/applications', 'data' => ['ids' => [$this->application->id]]],
            'individual' => ['method' => 'delete', 'url' => "/agent/applications/{$this->application->id}", 'data' => []],
        ];

        foreach ($methods as $type => $config) {
            $response = $this->json($config['method'], $config['url'], $config['data']);

            // Should fail (403, 404, or 405)
            $this->assertTrue(
                in_array($response->status(), [403, 404, 405]),
                "Agent should not be able to {$type} delete applications. Status: {$response->status()}"
            );
        }

        // Application should still exist
        $this->assertDatabaseHas('applications', [
            'id' => $this->application->id,
            'agent_id' => $this->agent->id,
        ]);
    }
}
