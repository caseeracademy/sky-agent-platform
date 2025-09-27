<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_agent_cannot_access_the_admin_panel(): void
    {
        $agent = User::factory()->create([
            'role' => 'agent_owner',
            'email' => 'agent@example.com',
        ]);

        $response = $this->actingAs($agent)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_agent_staff_cannot_access_the_admin_panel(): void
    {
        $agentStaff = User::factory()->create([
            'role' => 'agent_staff',
            'email' => 'agent.staff@example.com',
        ]);

        $response = $this->actingAs($agentStaff)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_middleware_allows_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'super@example.com',
        ]);
        $this->actingAs($superAdmin);

        $middleware = new \App\Http\Middleware\CheckUserRole();
        $request = \Illuminate\Http\Request::create('/admin');

        $response = $middleware->handle($request, function ($request) {
            return response('Admin access granted');
        }, 'super_admin', 'admin_staff');

        $this->assertEquals('Admin access granted', $response->getContent());
    }

    public function test_middleware_allows_admin_staff(): void
    {
        $adminStaff = User::factory()->create([
            'role' => 'admin_staff',
            'email' => 'admin@example.com',
        ]);
        $this->actingAs($adminStaff);

        $middleware = new \App\Http\Middleware\CheckUserRole();
        $request = \Illuminate\Http\Request::create('/admin');

        $response = $middleware->handle($request, function ($request) {
            return response('Admin access granted');
        }, 'super_admin', 'admin_staff');

        $this->assertEquals('Admin access granted', $response->getContent());
    }

    public function test_unauthenticated_user_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_check_user_role_middleware_blocks_wrong_roles(): void
    {
        $agent = User::factory()->create(['role' => 'agent_owner']);
        $this->actingAs($agent);

        $middleware = new \App\Http\Middleware\CheckUserRole();
        $request = \Illuminate\Http\Request::create('/admin');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Forbidden: Insufficient permissions');

        $middleware->handle($request, function ($request) {
            return response('Access granted');
        }, 'super_admin', 'admin_staff');
    }

    public function test_check_user_role_middleware_allows_correct_roles(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        $middleware = new \App\Http\Middleware\CheckUserRole();
        $request = \Illuminate\Http\Request::create('/admin');

        $response = $middleware->handle($request, function ($request) {
            return response('Access granted');
        }, 'super_admin', 'admin_staff');

        $this->assertEquals('Access granted', $response->getContent());
    }
}