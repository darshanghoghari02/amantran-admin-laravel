<?php

namespace App\Http\Middleware;

use App\Services\DbService;
use Closure;
use Illuminate\Http\Request;

/**
 * CheckSuspension — Port of Node.js checkSuspension middleware.
 *
 * Blocks requests from suspended/blocked app users.
 */
class CheckSuspension
{
    public function __construct(private DbService $db) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $userId = $request->header('x-user-id')
            ?? $request->attributes->get('jwt_user_id');

        if ($userId && $userId !== 'admin_super') {
            try {
                $user = $this->db->getOne('app_users', $userId);
                if ($user && ($user['isBlocked'] ?? false)) {
                    return response()->json([
                        'error'   => 'Account suspended',
                        'message' => 'Your account has been suspended. Please contact support.'
                    ], 403);
                }
                if ($user && ($user['accountStatus'] ?? 'active') === 'suspended') {
                    return response()->json([
                        'error'   => 'Account suspended',
                        'message' => 'Your account has been suspended. Please contact support.'
                    ], 403);
                }
            } catch (\Exception $e) {
                // Fail open — don't block if DB error
            }
        }

        return $next($request);
    }
}
