<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(function () {
                    Route::prefix('auth')->group(function () {
                        require_once __DIR__ . '/../routes/auth.php';
                    });
                    // Otras rutas API aquí
                    require_once __DIR__ . '/../routes/users.php';
                    require_once __DIR__ . '/../routes/statuses.php';
                    require_once __DIR__ . '/../routes/permissions.php';
                    require_once __DIR__ . '/../routes/roles.php';
                });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo de ModelNotFoundException para route model binding
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                $modelName = class_basename($e->getModel());
                $modelId = $e->getIds()[0] ?? 'unknown';

                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Resource Not Found',
                    'detail' => "The requested {$modelName} with ID {$modelId} was not found",
                    'errors' => "No {$modelName} found with the specified ID"
                ], 404);
            }
        });

        // Manejo de errores de autorización
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Access Denied',
                    'detail' => 'You do not have permission to perform this action',
                    'errors' => 'Unauthorized access'
                ], 403);
            }
        });

        // Manejo de errores de autenticación (incluyendo JWT)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                // Verificar si es un error de JWT basado en el mensaje
                $message = strtolower($e->getMessage());

                if (str_contains($message, 'blacklisted')) {
                    return response()->json([
                        'severity' => 'error',
                        'summary' => 'Token Invalid',
                        'detail' => 'Your authentication token has been invalidated',
                        'errors' => 'Please login again'
                    ], 401);
                } elseif (str_contains($message, 'expired')) {
                    return response()->json([
                        'severity' => 'error',
                        'summary' => 'Token Expired',
                        'detail' => 'Your authentication token has expired',
                        'errors' => 'Please refresh your token or login again'
                    ], 401);
                } elseif (str_contains($message, 'invalid') || str_contains($message, 'malformed')) {
                    return response()->json([
                        'severity' => 'error',
                        'summary' => 'Token Invalid',
                        'detail' => 'Your authentication token is invalid',
                        'errors' => 'Please provide a valid token'
                    ], 401);
                } else {
                    return response()->json([
                        'severity' => 'error',
                        'summary' => 'Authentication Required',
                        'detail' => 'You must be authenticated to access this resource',
                        'errors' => 'Unauthenticated'
                    ], 401);
                }
            }
        });

        // Manejo de errores de JWT
        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Token Expired',
                    'detail' => 'Your authentication token has expired',
                    'errors' => 'Please refresh your token or login again'
                ], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Token Invalid',
                    'detail' => 'Your authentication token has been invalidated',
                    'errors' => 'Please login again'
                ], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Token Invalid',
                    'detail' => 'Your authentication token is invalid',
                    'errors' => 'Please provide a valid token'
                ], 401);
            }
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\JWTException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Authentication Error',
                    'detail' => 'There was a problem with your authentication',
                    'errors' => 'Please check your token and try again'
                ], 401);
            }
        });

        // Manejo de errores de validación (por si acaso)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Validation Error',
                    'detail' => 'The provided data is invalid',
                    'errors' => $e->errors()
                ], 422);
            }
        });

        // Manejo genérico de errores para requests JSON
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                // No mostrar debug para errores de autenticación/JWT
                $isAuthError = $e instanceof \Illuminate\Auth\AuthenticationException ||
                    $e instanceof \Tymon\JWTAuth\Exceptions\JWTException ||
                    str_contains(get_class($e), 'Tymon\\JWTAuth') ||
                    str_contains(strtolower($e->getMessage()), 'token') ||
                    str_contains(strtolower($e->getMessage()), 'auth');

                // Solo mostrar detalles en desarrollo y no para errores de auth
                $debugInfo = (app()->environment('local') && !$isAuthError) ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null;

                return response()->json([
                    'severity' => 'error',
                    'summary' => 'Internal Server Error',
                    'detail' => 'An unexpected error occurred',
                    'errors' => app()->environment('local') ? $e->getMessage() : 'Something went wrong',
                    ...($debugInfo ? ['debug' => $debugInfo] : [])
                ], 500);
            }
        });
    })->create();
