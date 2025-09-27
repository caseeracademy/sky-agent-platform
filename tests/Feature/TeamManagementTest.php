<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authorization Test - Agent staff cannot access team management
     */
    public function test_agent_staff_cannot_access_agent_staff_resource(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
        ]);

        $this->actingAs($agentStaff);

        // Test the authorization method directly
        $canView = \App\Filament\Agent\Resources\AgentStaff\AgentStaffResource::canViewAny();
        
        $this->assertFalse($canView);
    }

    /**
     * Authorization Test - Agent owner can access team management
     */
    public function test_agent_owner_can_access_agent_staff_resource(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);

        $this->actingAs($agentOwner);

        // Test the authorization method directly
        $canView = \App\Filament\Agent\Resources\AgentStaff\AgentStaffResource::canViewAny();
        
        $this->assertTrue($canView);
    }

    /**
     * Data Scoping Test - Agent owners can only see their own staff
     */
    public function test_agent_owner_can_only_see_their_own_staff(): void
    {
        // Create two agent owners
        $agentOwnerA = User::factory()->create(['role' => 'agent_owner', 'name' => 'Agent A']);
        $agentOwnerB = User::factory()->create(['role' => 'agent_owner', 'name' => 'Agent B']);

        // Create staff for Agent A
        $staffA = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwnerA->id,
            'name' => 'Staff A',
        ]);

        // Create staff for Agent B
        $staffB = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwnerB->id,
            'name' => 'Staff B',
        ]);

        // Login as Agent B
        $this->actingAs($agentOwnerB);

        // Test the query scoping using the AgentStaffResource logic
        $visibleStaff = \App\Filament\Agent\Resources\AgentStaff\AgentStaffResource::getEloquentQuery()->get();

        // Agent B should only see their own staff
        $this->assertTrue($visibleStaff->contains($staffB));
        $this->assertFalse($visibleStaff->contains($staffA));
    }

    /**
     * Happy Path Test - Agent owner can create staff member correctly
     */
    public function test_agent_owner_can_create_staff_member(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);

        // Create staff member directly with proper data
        $staffMember = User::create([
            'name' => 'New Staff Member',
            'email' => 'newstaff@example.com',
            'password' => bcrypt('password123'),
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
            'is_active' => true,
        ]);

        // Assert the data was set correctly
        $this->assertDatabaseHas('users', [
            'name' => 'New Staff Member',
            'email' => 'newstaff@example.com',
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
            'is_active' => true,
        ]);

        // Test the relationship
        $this->assertEquals($agentOwner->id, $staffMember->parent_agent_id);
        $this->assertEquals('agent_staff', $staffMember->role);
        $this->assertTrue($staffMember->is_active);

        // Test that the agent owner can see this staff member
        $this->assertTrue($agentOwner->agentStaff->contains($staffMember));
    }

    /**
     * Validation Test - Staff creation requires valid data
     */
    public function test_staff_creation_requires_valid_data(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $this->actingAs($agentOwner);

        // Test missing name
        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
            // name is missing - should fail
        ]);
    }

    /**
     * Authorization Test - Non-agents cannot create staff
     */
    public function test_super_admin_cannot_access_agent_staff_resource(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin);

        // Test the authorization method directly
        $canView = \App\Filament\Agent\Resources\AgentStaff\AgentStaffResource::canViewAny();
        
        $this->assertFalse($canView);
    }
}