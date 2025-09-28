<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('filament.agent.auth.login');
        }

        $user = auth()->user();
        
        // Debug logging for production issues
        if (config('app.debug')) {
            \Log::info('EnsureUserIsAgent Middleware', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'url' => $request->url()
            ]);
        }
        
        // Check if user is an agent (agent_owner or agent_staff)
        if (! in_array($user->role, ['agent_owner', 'agent_staff'])) {
            \Log::warning('Non-agent user attempted to access agent portal', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'url' => $request->url()
            ]);
            
            auth()->logout();
            return redirect()->route('filament.agent.auth.login')
                ->withErrors(['email' => 'Access denied. Only agents can access this portal. Your role: ' . $user->role]);
        }

        return $next($request);
    }
}
