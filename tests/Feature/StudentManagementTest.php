<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $agentOwner;
    protected User $agentStaff;

    protected function setUp(): void
    {
        parent::setUp();

        // Create agent users
        $this->agentOwner = User::factory()->create([
            'role' => 'agent_owner',
            'name' => 'Test Agent Owner',
            'email' => 'agent.owner@test.com',
        ]);

        $this->agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'name' => 'Test Agent Staff',
            'email' => 'agent.staff@test.com',
            'parent_agent_id' => $this->agentOwner->id,
        ]);
    }

    public function test_agent_can_create_student(): void
    {
        $studentData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone_number' => '+1234567890',
            'country_of_residence' => 'United States',
            'date_of_birth' => '2000-01-01',
        ];

        $student = Student::create(array_merge($studentData, [
            'agent_id' => $this->agentOwner->id,
        ]));

        $this->assertDatabaseHas('students', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agent_id' => $this->agentOwner->id,
        ]);

        $this->assertEquals($this->agentOwner->id, $student->agent_id);
        $this->assertEquals('John Doe', $student->name);
    }

    public function test_student_belongs_to_agent(): void
    {
        $student = Student::factory()->create([
            'agent_id' => $this->agentOwner->id,
        ]);

        $this->assertEquals($this->agentOwner->id, $student->agent->id);
        $this->assertEquals($this->agentOwner->name, $student->agent->name);
    }

    public function test_agent_has_many_students(): void
    {
        $students = Student::factory(3)->create([
            'agent_id' => $this->agentOwner->id,
        ]);

        $this->assertCount(3, $this->agentOwner->students);
        $this->assertTrue($this->agentOwner->students->contains($students[0]));
    }

    public function test_student_email_unique_per_agent(): void
    {
        // Create student for agent owner
        Student::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'email' => 'duplicate@example.com',
        ]);

        // Same email for different agent should work
        $studentForStaff = Student::factory()->create([
            'agent_id' => $this->agentStaff->id,
            'email' => 'duplicate@example.com',
        ]);

        $this->assertDatabaseHas('students', [
            'email' => 'duplicate@example.com',
            'agent_id' => $this->agentOwner->id,
        ]);

        $this->assertDatabaseHas('students', [
            'email' => 'duplicate@example.com',
            'agent_id' => $this->agentStaff->id,
        ]);
    }

    public function test_student_for_agent_scope(): void
    {
        // Create students for different agents
        $ownerStudent = Student::factory()->create(['agent_id' => $this->agentOwner->id]);
        $staffStudent = Student::factory()->create(['agent_id' => $this->agentStaff->id]);

        $ownerStudents = Student::forAgent($this->agentOwner->id)->get();
        $staffStudents = Student::forAgent($this->agentStaff->id)->get();

        $this->assertCount(1, $ownerStudents);
        $this->assertCount(1, $staffStudents);
        $this->assertTrue($ownerStudents->contains($ownerStudent));
        $this->assertTrue($staffStudents->contains($staffStudent));
        $this->assertFalse($ownerStudents->contains($staffStudent));
        $this->assertFalse($staffStudents->contains($ownerStudent));
    }

    public function test_student_age_calculation(): void
    {
        $birthDate = now()->subYears(25)->format('Y-m-d');
        
        $student = Student::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'date_of_birth' => $birthDate,
        ]);

        $this->assertEquals(25, $student->age);
    }

    public function test_student_display_name(): void
    {
        $student = Student::factory()->create([
            'agent_id' => $this->agentOwner->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $this->assertEquals('Jane Smith (jane@example.com)', $student->display_name);
    }
}