<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request and check if user has one of the required roles.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'UNAUTHENTICATED'
            ], 401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Support check via user->hasRole(...) or direct role field
        $hasRole = false;
        if (method_exists($user, 'hasAnyRole')) {
            $hasRole = $user->hasAnyRole($roles);
        } elseif (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }
        } elseif (isset($user->role)) {
            $hasRole = in_array($user->role, $roles);
        }

        if (!$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to access this resource.',
                'error_code' => 'UNAUTHORIZED_ROLE',
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}
