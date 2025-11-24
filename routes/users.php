<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Nota: los administradores (admin / super admin) tienen privilegios extendidos
// en las policies y en el middleware 'permission' se ha configurado para
// permitir que estos roles hagan bypass de comprobaciones de permisos.
// Para forzar acceso exclusivamente por rol, use ->middleware(['role:admin']).
Route::middleware(['jwt.auth'])->group(function () {
    // Crear usuario - requiere permiso 'create users'
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create users');

    // Ver usuarios - requiere permiso 'view users'
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view users');

    // Ver usuario específico - requiere permiso 'view users'
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:view users');

    // Actualizar usuario - requiere permiso 'edit users'
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit users');

    // Eliminar usuario - requiere permiso 'delete users'
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete users');
});
