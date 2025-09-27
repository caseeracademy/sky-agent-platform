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
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is an agent (agent_owner or agent_staff)
            if (! in_array($user->role, ['agent_owner', 'agent_staff'])) {
                auth()->logout();
                return redirect()->route('filament.agent.auth.login')
                    ->withErrors(['email' => 'Access denied. Only agents can access this portal.']);
            }
        }

        return $next($request);
    }
}
