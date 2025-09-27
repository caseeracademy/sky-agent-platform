<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test for Admin User Visibility - The Authorization Test
     */
    public function test_super_admin_can_see_agent_owners_but_not_agent_staff(): void
    {
        // Create users
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
        ]);

        $this->actingAs($superAdmin);

        // Test the query directly using the UserResource logic
        $visibleUsers = \App\Filament\Resources\Users\UserResource::getEloquentQuery()->get();

        // Super Admin should see agent_owner but not agent_staff
        $this->assertTrue($visibleUsers->contains($superAdmin));
        $this->assertTrue($visibleUsers->contains($agentOwner));
        $this->assertFalse($visibleUsers->contains($agentStaff));
    }

    /**
     * Test that admin staff also follows the same visibility rules
     */
    public function test_admin_staff_can_see_agent_owners_but_not_agent_staff(): void
    {
        // Create users
        $adminStaff = User::factory()->create(['role' => 'admin_staff']);
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
        ]);

        $this->actingAs($adminStaff);

        // Test the query directly
        $visibleUsers = \App\Filament\Resources\Users\UserResource::getEloquentQuery()->get();

        // Admin Staff should see agent_owner but not agent_staff
        $this->assertTrue($visibleUsers->contains($adminStaff));
        $this->assertTrue($visibleUsers->contains($agentOwner));
        $this->assertFalse($visibleUsers->contains($agentStaff));
    }

    /**
     * Validation Test - Role validation (Note: SQLite doesn't enforce enum constraints)
     */
    public function test_user_role_validation_in_application_logic(): void
    {
        $validRoles = ['super_admin', 'admin_staff', 'agent_owner', 'agent_staff'];
        
        // Test that our middleware properly validates roles
        $user = User::factory()->create(['role' => 'invalid_role']);
        $this->actingAs($user);

        $middleware = new \App\Http\Middleware\CheckUserRole();
        $request = \Illuminate\Http\Request::create('/admin');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($request) {
            return response('Access granted');
        }, ...$validRoles);
    }

    /**
     * Happy Path Test - User creation works with valid data
     */
    public function test_users_can_be_created_with_valid_roles(): void
    {
        $validRoles = ['super_admin', 'admin_staff', 'agent_owner', 'agent_staff'];

        foreach ($validRoles as $role) {
            $user = User::create([
                'name' => "Test {$role}",
                'email' => "{$role}@example.com",
                'password' => bcrypt('password'),
                'role' => $role,
                'is_active' => true,
            ]);

            $this->assertDatabaseHas('users', [
                'email' => "{$role}@example.com",
                'role' => $role,
            ]);
        }
    }

    /**
     * Data Scoping Test - Agent staff hierarchy
     */
    public function test_agent_staff_belongs_to_correct_parent(): void
    {
        $agentOwnerA = User::factory()->create(['role' => 'agent_owner', 'name' => 'Agent A']);
        $agentOwnerB = User::factory()->create(['role' => 'agent_owner', 'name' => 'Agent B']);

        $staffA = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwnerA->id,
            'name' => 'Staff A',
        ]);

        $staffB = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwnerB->id,
            'name' => 'Staff B',
        ]);

        // Test relationships
        $this->assertEquals($agentOwnerA->id, $staffA->parent_agent_id);
        $this->assertEquals($agentOwnerB->id, $staffB->parent_agent_id);

        // Test that Agent A can see their staff but not Agent B's staff
        $agentAStaff = $agentOwnerA->agentStaff;
        $this->assertTrue($agentAStaff->contains($staffA));
        $this->assertFalse($agentAStaff->contains($staffB));
    }
}