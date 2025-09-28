<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $user = auth()->user();

        // Debug logging for production issues
        if (config('app.debug')) {
            \Log::info('CheckUserRole Middleware', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'url' => $request->url()
            ]);
        }

        if (!in_array($user->role, $roles)) {
            \Log::warning('Access denied for user', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'url' => $request->url()
            ]);
            
            abort(403, 'Forbidden: Insufficient permissions. Required roles: ' . implode(', ', $roles) . '. Your role: ' . $user->role);
        }

        return $next($request);
    }
}
