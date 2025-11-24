<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'severity' => 'error',
                'summary' => 'Unauthorized',
                'detail' => 'Authentication required',
            ], 401);
        }

        $user = Auth::user();

        // Allow admins or super admins to bypass permission checks. This mirrors
        // the policies where 'admin' or 'super admin' roles have full access.
        if ($user->hasRole('admin') || $user->hasRole('super admin')) {
            return $next($request);
        }

        // Otherwise, validate the permission as usual
        if (!$user->hasPermissionTo($permission)) {
            return response()->json([
                'severity' => 'error',
                'summary' => 'Forbidden',
                'detail' => 'You do not have permission to access this resource',
            ], 403);
        }

        return $next($request);
    }
}
