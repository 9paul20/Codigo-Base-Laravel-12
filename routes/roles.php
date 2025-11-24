<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Roles Management
|--------------------------------------------------------------------------
|
| Este archivo contiene las rutas para gestión de roles con diferentes
| patrones de autorización usando middlewares.
|
| MIDDLEWARES DISPONIBLES:
| - jwt.auth: Autenticación JWT requerida
| - permission:name: Validación de permisos específicos
| - role:name: Validación de roles específicos
|
| PATRONES DE AUTORIZACIÓN:
| 1. Solo permisos: permission:view roles
| 2. Solo roles: role:admin
| 3. Combinado: role:admin + permission:edit roles
| 4. Múltiples: role:admin|moderator + permission:create|edit
|
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| DEMO ROUTES - Ejemplos de uso de middlewares
|--------------------------------------------------------------------------
|
| Estas rutas demuestran diferentes patrones de autorización.
| En producción, reemplazar closures con controladores reales.
|
*/

// ┌─────────────────────────────────────────────────────────────────────────┐
// │ PATRÓN 1: AUTORIZACIÓN BASADA SOLO EN ROLES                           │
// │ Útil cuando el rol determina completamente el acceso                  │
// └─────────────────────────────────────────────────────────────────────────┘

// Solo administradores - Acceso completo al panel admin
Route::middleware(['jwt.auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return response()->json([
            'message' => 'Welcome to admin dashboard',
            'authorized_by' => 'role:admin'
        ]);
    });

    Route::get('/admin/users', function () {
        return response()->json([
            'message' => 'Admin users management',
            'authorized_by' => 'role:admin'
        ]);
    });
});

// Administradores o moderadores - Gestión de contenido
Route::middleware(['jwt.auth', 'role:admin|moderator'])->group(function () {
    Route::get('/manage/content', function () {
        return response()->json([
            'message' => 'Content management for admins and moderators',
            'authorized_by' => 'role:admin|moderator'
        ]);
    });
});

// Solo super administradores - Configuración del sistema
Route::middleware(['jwt.auth', 'role:super admin'])->group(function () {
    Route::get('/system/settings', function () {
        return response()->json([
            'message' => 'System settings - Super Admin only',
            'authorized_by' => 'role:super admin'
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| CRUD ROUTES - Gestión de Roles (Producción)
|--------------------------------------------------------------------------
|
| Estas son las rutas reales para gestión de roles usando diferentes
| estrategias de autorización según la operación.
|
*/

// ┌─────────────────────────────────────────────────────────────────────────┐
// │ LECTURA: Solo permisos específicos                                   │
// │ Cualquier usuario con permiso 'view roles' puede ver roles           │
// └─────────────────────────────────────────────────────────────────────────┘
Route::middleware(['jwt.auth', 'permission:view roles'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
});

// ┌─────────────────────────────────────────────────────────────────────────┐
// │ CREACIÓN: Permisos múltiples                                         │
// │ Usuario necesita permisos 'create roles' O 'update roles'            │
// └─────────────────────────────────────────────────────────────────────────┘
Route::middleware(['jwt.auth', 'permission:create roles|update roles'])->group(function () {
    Route::post('/roles', [RoleController::class, 'store']);
});

// ┌─────────────────────────────────────────────────────────────────────────┐
// │ ACTUALIZACIÓN: Rol + Permiso                                         │
// │ Solo administradores CON permiso específico 'edit roles'             │
// └─────────────────────────────────────────────────────────────────────────┘
Route::middleware(['jwt.auth', 'role:admin', 'permission:edit roles'])->group(function () {
    Route::put('/roles/{id}', [RoleController::class, 'update']);
});

// ┌─────────────────────────────────────────────────────────────────────────┐
// │ ELIMINACIÓN: Múltiples roles + Múltiples permisos                    │
// │ Admin O Super admin CON (edit roles O delete roles)                  │
// └─────────────────────────────────────────────────────────────────────────┘
Route::middleware(['jwt.auth', 'role:admin|super admin', 'permission:edit roles|delete roles'])->group(function () {
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| NOTAS IMPORTANTES
|--------------------------------------------------------------------------
|
| 1. El orden de middlewares importa: jwt.auth debe ir primero
| 2. Los middlewares se evalúan en orden, fallando en el primero que no pase
| 3. Para nombrar roles prefiera 'super admin' (con espacios) para consistencia con los datos sembrados.
| 4. Combinar 'role' + 'permission' ofrece máxima seguridad y flexibilidad
| 5. Usar 'role' solo cuando el rol determine completamente el acceso
| 6. Usar 'permission' para acceso granular y específico
|
*/
