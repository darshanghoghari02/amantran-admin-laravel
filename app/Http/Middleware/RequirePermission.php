<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;

/**
 * RequirePermission — Middleware for checking admin action-level permissions.
 *
 * Usage in routes: ->middleware('require.permission:templates.create')
 */
class RequirePermission
{
    public function __construct(private PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        $userId = $request->header('x-user-id')
            ?? session('admin_user.id')
            ?? null;

        if (!$userId) {
            return response()->json(['error' => 'Missing x-user-id header or session.'], 401);
        }

        if (!$this->permissions->hasPermission($userId, $permission)) {
            return response()->json([
                'error' => "Forbidden. You do not have permission: {$permission}"
            ], 403);
        }

        return $next($request);
    }
}
