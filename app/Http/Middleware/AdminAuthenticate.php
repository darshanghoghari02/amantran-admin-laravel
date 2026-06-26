<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;

/**
 * AdminAuthenticate — Middleware for admin panel session-based auth.
 *
 * Protects all /admin/* routes. Redirects to login if not authenticated.
 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!session()->has('admin_user')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        return $next($request);
    }
}
