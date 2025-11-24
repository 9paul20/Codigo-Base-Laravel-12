<?php

use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

// Nota: los administradores (admin / super admin) tienen permisos extendidos
// en las policies; además, el middleware 'permission' permite que estos roles
// omitan la verificación de permisos. Para forzar por rol use role:admin.
Route::middleware(['jwt.auth'])->group(function () {
    // Crear status - requiere permiso 'create permissions' (solo admin)
    Route::post('/statuses', [StatusController::class, 'store'])->middleware('permission:create permissions');

    // Ver statuses - requiere permiso 'view permissions'
    Route::get('/statuses', [StatusController::class, 'index'])->middleware('permission:view permissions');

    // Ver status específico - requiere permiso 'view permissions'
    Route::get('/statuses/{status}', [StatusController::class, 'show'])->middleware('permission:view permissions');

    // Actualizar status - requiere permiso 'edit permissions'
    Route::put('/statuses/{status}', [StatusController::class, 'update'])->middleware('permission:edit permissions');

    // Eliminar status - requiere permiso 'delete permissions'
    Route::delete('/statuses/{status}', [StatusController::class, 'destroy'])->middleware('permission:delete permissions');
});
