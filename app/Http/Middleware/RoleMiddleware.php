<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Check if user's role is in the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Access denied - insufficient permissions');
    }
}
