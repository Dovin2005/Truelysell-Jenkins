<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure; // Make sure to import Closure if not already imported
use Illuminate\Support\Facades\Auth; // Make sure to import Auth

class Authenticate extends Middleware
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('/');
        }

        return $next($request);
    }
}
