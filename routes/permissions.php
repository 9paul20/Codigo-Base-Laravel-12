<?php

use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

// Nota: el middleware 'permission' permite accesos granulares; además el
// middleware se ha ajustado para que roles 'admin' o 'super admin' puedan
// omitir la comprobación de permisos (bypass) — esto sigue la lógica de
// las policies donde administradores tienen acceso completo.
// Si desea forzar un acceso por rol en lugar de permiso, puede usar:
// ->middleware(['jwt.auth', 'role:admin|super admin'])
Route::middleware(['jwt.auth'])->group(function () {
    // Crear permiso - requiere permiso 'create permissions'
    Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:create permissions',);

    // Ver permisos - requiere permiso 'view permissions'
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:view permissions');

    // Ver permiso específico - requiere permiso 'view permissions'
    Route::get('/permissions/{id}', [PermissionController::class, 'show'])->middleware('permission:view permissions');

    // Actualizar permiso - requiere permiso 'edit permissions'
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->middleware('permission:edit permissions');

    // Eliminar permiso - requiere permiso 'delete permissions'
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->middleware('permission:delete permissions');
});
