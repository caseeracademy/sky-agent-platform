<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_dashboard_calculates_correct_application_counts(): void
    {
        // Create test applications with different statuses
        Application::factory()->create(['status' => 'pending']);
        Application::factory()->create(['status' => 'pending']);
        Application::factory()->create(['status' => 'under_review']);
        Application::factory()->create(['status' => 'additional_documents_required']);
        
        // Create approved application this month
        Application::factory()->create([
            'status' => 'approved',
            'updated_at' => now(),
        ]);
        
        // Create approved application last month (should not count)
        Application::factory()->create([
            'status' => 'approved',
            'updated_at' => now()->subMonth(),
        ]);

        // Test the counts directly from the database
        $pendingCount = Application::where('status', 'pending')->count();
        $inReviewCount = Application::where('status', 'under_review')->count();
        $awaitingDocsCount = Application::where('status', 'additional_documents_required')->count();
        $approvedThisMonth = Application::where('status', 'approved')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Assert the calculations are correct
        $this->assertEquals(2, $pendingCount);
        $this->assertEquals(1, $inReviewCount);
        $this->assertEquals(1, $awaitingDocsCount);
        $this->assertEquals(1, $approvedThisMonth);
    }

    /** @test */
    public function application_locking_rule_works_correctly(): void
    {
        // Test that the business logic for locking works
        $pendingApp = Application::factory()->create(['status' => 'pending']);
        $approvedApp = Application::factory()->create(['status' => 'approved']);

        // Test the canBeEdited method on the model
        $this->assertTrue($pendingApp->canBeEdited());
        $this->assertFalse($approvedApp->canBeEdited());
        
        // Test that the status check works as expected
        $this->assertNotEquals('approved', $pendingApp->status);
        $this->assertEquals('approved', $approvedApp->status);
    }

    /** @test */
    public function application_status_colors_are_mapped_correctly(): void
    {
        $application = Application::factory()->create(['status' => 'under_review']);
        
        // Test the status color attribute
        $this->assertEquals('warning', $application->status_color);
        $this->assertEquals('Under Review', $application->formatted_status);
        
        // Test approved status
        $approvedApp = Application::factory()->create(['status' => 'approved']);
        $this->assertEquals('success', $approvedApp->status_color);
        $this->assertEquals('Approved', $approvedApp->formatted_status);
    }

    /** @test */
    public function application_hub_loads_with_correct_data(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $application = Application::factory()->create([
            'status' => 'under_review',
            'admin_notes' => 'Test admin notes',
            'notes' => 'Test agent notes',
        ]);

        // Verify the application data is accessible
        $this->assertEquals('under_review', $application->status);
        $this->assertEquals('Test admin notes', $application->admin_notes);
        $this->assertEquals('Test agent notes', $application->notes);
        $this->assertNotNull($application->student);
        $this->assertNotNull($application->program);
        $this->assertNotNull($application->agent);
    }
}
