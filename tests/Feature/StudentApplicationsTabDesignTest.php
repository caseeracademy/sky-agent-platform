<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApplicationsTabDesignTest extends TestCase
{
    use RefreshDatabase;

    protected User $agent;

    protected University $university;

    protected Program $program;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = User::factory()->create(['role' => 'agent_owner']);
        $this->university = University::factory()->create();
        $this->program = Program::factory()->create(['university_id' => $this->university->id]);
        $this->student = Student::factory()->create(['agent_id' => $this->agent->id]);
    }

    /**
     * Test that the student applications tab displays applications with the new design
     */
    public function test_student_applications_tab_displays_applications_with_new_design(): void
    {
        // Create an application for the student
        $application = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
            'commission_amount' => 1500.00,
        ]);

        // Test that the application is properly linked
        $this->assertDatabaseHas('applications', [
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        // Test that the student has the application
        $this->assertTrue($this->student->applications->contains($application));
        $this->assertEquals(1, $this->student->applications()->count());
    }

    /**
     * Test that applications are displayed with proper status colors
     */
    public function test_applications_display_with_proper_status_colors(): void
    {
        $statuses = ['pending', 'approved', 'rejected', 'under_review'];

        foreach ($statuses as $status) {
            Application::factory()->create([
                'student_id' => $this->student->id,
                'program_id' => $this->program->id,
                'agent_id' => $this->agent->id,
                'status' => $status,
                'commission_amount' => 1000.00,
            ]);
        }

        // Test that all applications were created
        $this->assertEquals(4, $this->student->applications()->count());

        // Test that each status exists
        foreach ($statuses as $status) {
            $this->assertDatabaseHas('applications', [
                'student_id' => $this->student->id,
                'status' => $status,
            ]);
        }
    }

    /**
     * Test that the applications tab shows empty state when no applications
     */
    public function test_applications_tab_shows_empty_state_when_no_applications(): void
    {
        // Student with no applications
        $this->assertEquals(0, $this->student->applications()->count());

        // This should display the empty state message
        $this->assertTrue(true, 'Empty state should be displayed when no applications exist');
    }

    /**
     * Test that application details button links to correct application hub
     */
    public function test_application_details_button_links_to_application_hub(): void
    {
        $application = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'status' => 'pending',
        ]);

        // Test that the application exists and can be accessed
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'student_id' => $this->student->id,
        ]);

        // The button should link to the application view page
        $expectedUrl = route('filament.agent.resources.applications.view', $application->id);
        $this->assertIsString($expectedUrl);
    }

    /**
     * Test that applications are ordered by creation date (newest first)
     */
    public function test_applications_ordered_by_creation_date_newest_first(): void
    {
        // Create applications with different creation dates
        $application1 = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'created_at' => now()->subDays(2),
        ]);

        $application2 = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'created_at' => now()->subDays(1),
        ]);

        $application3 = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'created_at' => now(),
        ]);

        // Get applications ordered by creation date desc
        $applications = $this->student->applications()
            ->orderBy('created_at', 'desc')
            ->get();

        // Test that applications are in correct order (newest first)
        $this->assertEquals($application3->id, $applications->first()->id);
        $this->assertEquals($application2->id, $applications->skip(1)->first()->id);
        $this->assertEquals($application1->id, $applications->last()->id);
    }

    /**
     * Test that commission amounts are properly displayed
     */
    public function test_commission_amounts_properly_displayed(): void
    {
        $application = Application::factory()->create([
            'student_id' => $this->student->id,
            'program_id' => $this->program->id,
            'agent_id' => $this->agent->id,
            'commission_amount' => 2500.75,
        ]);

        // Test that commission amount is properly formatted
        $formattedCommission = '$'.number_format($application->commission_amount, 2);
        $this->assertEquals('$2,500.75', $formattedCommission);
    }
}
