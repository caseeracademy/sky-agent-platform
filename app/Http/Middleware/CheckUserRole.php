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
            abort(401, 'Unauthorized');
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            abort(403, 'Forbidden: Insufficient permissions');
        }

        return $next($request);
    }
}
