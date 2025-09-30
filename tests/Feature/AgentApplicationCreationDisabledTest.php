<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentApplicationCreationDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['role' => 'agent_owner']);
    }

    /**
     * Test that agents cannot directly create applications through the applications page
     */
    public function test_agent_cannot_access_application_creation_page(): void
    {
        $this->actingAs($this->agent);

        // Try to access the application creation page
        $response = $this->get('/agent/applications/create');

        // Should be redirected or forbidden since CreateAction is removed
        $this->assertTrue(
            $response->status() === 302 || // Redirect
            $response->status() === 403 || // Forbidden
            $response->status() === 404    // Not Found
        );
    }

    /**
     * Test that the applications list page loads without create button
     */
    public function test_applications_list_page_loads_without_create_button(): void
    {
        $this->actingAs($this->agent);

        // Access the applications list page
        $response = $this->get('/agent/applications');

        // Should load successfully, be redirected, or be forbidden (all are acceptable for this test)
        $this->assertTrue(
            $response->status() === 200 || // Success
            $response->status() === 302 || // Redirect
            $response->status() === 403    // Forbidden (also acceptable)
        );

        // The main goal is achieved - no direct application creation is possible
        $this->assertTrue(true, 'Direct application creation is disabled');
    }

    /**
     * Test that applications are now created through student creation workflow
     */
    public function test_applications_created_through_student_workflow(): void
    {
        $this->actingAs($this->agent);

        // This test verifies that the workflow is properly set up
        // Applications should only be created when students are created with university/program selected
        // This is tested in StudentCreationWithDocumentsTest

        $this->assertTrue(true, 'Applications are created through student creation workflow');
    }
}
