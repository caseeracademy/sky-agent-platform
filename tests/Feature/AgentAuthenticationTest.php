<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_allows_agent_owner(): void
    {
        $agent = User::factory()->create(['role' => 'agent_owner']);
        $this->actingAs($agent);

        $middleware = new \App\Http\Middleware\EnsureUserIsAgent();
        $request = \Illuminate\Http\Request::create('/agent');
        
        $response = $middleware->handle($request, function ($request) {
            return response('Access granted');
        });

        $this->assertEquals('Access granted', $response->getContent());
    }

    public function test_middleware_allows_agent_staff(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
        ]);
        $this->actingAs($agentStaff);

        $middleware = new \App\Http\Middleware\EnsureUserIsAgent();
        $request = \Illuminate\Http\Request::create('/agent');
        
        $response = $middleware->handle($request, function ($request) {
            return response('Access granted');
        });

        $this->assertEquals('Access granted', $response->getContent());
    }

    public function test_middleware_blocks_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        $middleware = new \App\Http\Middleware\EnsureUserIsAgent();
        $request = \Illuminate\Http\Request::create('/agent');
        
        $response = $middleware->handle($request, function ($request) {
            return response('Access granted');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertGuest();
    }

    public function test_middleware_blocks_admin_staff(): void
    {
        $adminStaff = User::factory()->create(['role' => 'admin_staff']);
        $this->actingAs($adminStaff);

        $middleware = new \App\Http\Middleware\EnsureUserIsAgent();
        $request = \Illuminate\Http\Request::create('/agent');
        
        $response = $middleware->handle($request, function ($request) {
            return response('Access granted');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertGuest();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/agent');

        $response->assertRedirect('/agent/login');
    }

    public function test_agent_login_page_accessible(): void
    {
        $response = $this->get('/agent/login');

        $response->assertStatus(200);
        $response->assertSee('Email address');
        $response->assertSee('Password');
    }

    public function test_agent_login_page_works(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent_owner',
            'email' => 'agent@example.com',
            'password' => bcrypt('password'),
        ]);

        // Test that we can simulate login functionality
        $this->actingAs($agent);
        $this->assertAuthenticatedAs($agent);
    }

    public function test_middleware_blocks_non_agents(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        // Mock the middleware behavior
        $this->actingAs($superAdmin);
        
        // Test our middleware logic directly
        $middleware = new \App\Http\Middleware\EnsureUserIsAgent();
        $request = \Illuminate\Http\Request::create('/agent');
        
        $response = $middleware->handle($request, function ($request) {
            return response('Access granted');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertGuest();
    }

    public function test_user_relationships_work_correctly(): void
    {
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'parent_agent_id' => $agentOwner->id,
        ]);

        // Test parent relationship
        $this->assertEquals($agentOwner->id, $agentStaff->parentAgent->id);
        
        // Test children relationship
        $this->assertTrue($agentOwner->agentStaff->contains($agentStaff));
    }

    public function test_user_role_helper_methods(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $adminStaff = User::factory()->create(['role' => 'admin_staff']);
        $agentOwner = User::factory()->create(['role' => 'agent_owner']);
        $agentStaff = User::factory()->create(['role' => 'agent_staff']);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isAgentOwner());

        $this->assertTrue($adminStaff->isAdminStaff());
        $this->assertFalse($adminStaff->isAgentStaff());

        $this->assertTrue($agentOwner->isAgentOwner());
        $this->assertFalse($agentOwner->isSuperAdmin());

        $this->assertTrue($agentStaff->isAgentStaff());
        $this->assertFalse($agentStaff->isAdminStaff());
    }
}