<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreventAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and is an admin
        if (Auth::check() && Auth::user()->user_type === 1) {
            // Redirect the admin to the dashboard or any other route
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
