<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivewireMultipleRootElementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['role' => 'agent_owner']);

        $this->application = Application::factory()->create([
            'agent_id' => $this->agent->id,
            'status' => 'additional_documents_required',
            'additional_documents_request' => 'Please provide updated transcript and IELTS certificate.',
        ]);
    }

    public function test_additional_documents_warning_component_renders_correctly(): void
    {
        // Test that our warning component renders without errors
        $view = view('filament.components.additional-documents-warning-simple', [
            'request' => 'Please provide updated transcript and IELTS certificate.',
        ]);

        $rendered = $view->render();

        // Check that the warning panel content is present
        $this->assertStringContainsString('Please Upload Missing Documents', $rendered);
        $this->assertStringContainsString('Please provide updated transcript and IELTS certificate.', $rendered);
        $this->assertStringContainsString('Upload Documents', $rendered);
        $this->assertStringContainsString('View Documents', $rendered);
        $this->assertStringContainsString('warning-panel', $rendered);
    }

    public function test_warning_component_shows_upload_button_without_modal(): void
    {
        // Test that the warning component shows upload button without modal (temporary fix)
        $view = view('filament.components.additional-documents-warning-simple', [
            'request' => 'Please provide updated transcript and IELTS certificate.',
        ]);

        $rendered = $view->render();

        // Check that the warning content is present
        $this->assertStringContainsString('Please Upload Missing Documents', $rendered);
        $this->assertStringContainsString('Upload Documents', $rendered);
        $this->assertStringContainsString('View Documents', $rendered);

        // Check that modal is NOT present (temporary fix)
        $this->assertStringNotContainsString('uploadModal', $rendered);
        $this->assertStringNotContainsString('Resubmit Application', $rendered);
    }

    public function test_view_application_page_template_exists(): void
    {
        // Test that our custom page template exists and is valid
        $templatePath = resource_path('views/filament/agent/resources/applications/pages/view-application.blade.php');

        $this->assertFileExists($templatePath);

        $content = file_get_contents($templatePath);

        // Check that it's a simple page template (modal is now in warning component)
        $this->assertStringContainsString('filament-panels::page', $content);
    }
}
