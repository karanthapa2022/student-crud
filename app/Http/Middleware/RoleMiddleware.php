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
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        // User must be authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // User's role
        $userRole = $request->user()->role;

        // Check whether user's role is allowed
        if (!in_array($userRole, $roles, true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}