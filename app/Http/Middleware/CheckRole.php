<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Roles separados por | (ej: 'admin|moderator')
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'severity' => 'error',
                'summary' => 'Unauthorized',
                'detail' => 'Authentication required',
            ], 401);
        }

        $user = Auth::user();

        // Convertir string de roles separados por | en array
        $roleArray = explode('|', $roles);

        // Verificar si el usuario tiene al menos uno de los roles
        $hasRole = false;
        foreach ($roleArray as $role) {
            if ($user->hasRole(trim($role))) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return response()->json([
                'severity' => 'error',
                'summary' => 'Forbidden',
                'detail' => 'You do not have the required role to access this resource',
            ], 403);
        }

        return $next($request);
    }
}
