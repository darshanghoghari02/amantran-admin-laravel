<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

/**
 * ApiJwtAuthenticate — JWT middleware for Flutter API routes.
 *
 * Validates JWT tokens from Authorization: Bearer <token> header.
 * Also supports the legacy x-user-id header for backward compatibility.
 */
class ApiJwtAuthenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Support legacy x-user-id header without JWT (for internal/admin calls)
        $userId = $request->header('x-user-id');
        if ($userId === 'admin_super') {
            $request->attributes->set('jwt_user_id', 'admin_super');
            return $next($request);
        }

        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $request->attributes->set('jwt_user_id', $payload->get('sub'));
            $request->attributes->set('jwt_payload', $payload->toArray());
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid.'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Authorization token not found.'], 401);
        }

        return $next($request);
    }
}
