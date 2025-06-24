<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveEnrollment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to student users
        if (auth()->check() && auth()->user()->role === 'student') {
            $student = auth()->user()->student;

            // If no student record or no active enrollments, restrict access
            if (! $student || ! $student->hasActiveEnrollments()) {
                return redirect()->route('dashboard')
                    ->with('warning', 'You do not have any active enrollments and cannot access this page.');
            }
        }

        return $next($request);
    }
}
