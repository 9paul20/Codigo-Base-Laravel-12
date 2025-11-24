<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Usuarios autenticados pueden ver la lista de usuarios
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $targetUser): bool
    {
        // Usuarios autenticados pueden ver detalles de usuarios
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo usuarios con permisos administrativos pueden crear usuarios
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $targetUser): bool
    {
        // Solo usuarios con permisos administrativos pueden actualizar usuarios
        if (!$user->hasRole('admin') && !$user->hasRole('super admin') && !$user->hasPermissionTo('edit users')) {
            return false;
        }

        // No permitir cambiar el estatus de uno mismo
        if ($targetUser->id === $user->id) {
            return true; // Permitir actualizar otros campos de sí mismo
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Solo usuarios con permisos administrativos pueden eliminar usuarios
        if (!$user->hasRole('admin') && !$user->hasRole('super admin') && !$user->hasPermissionTo('delete users')) {
            return false;
        }

        // No permitir que un usuario se elimine a sí mismo
        if ($targetUser->id === $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $targetUser): bool
    {
        // Solo super admin puede restaurar usuarios (si se implementa soft delete)
        return $user->hasRole('super admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        // Solo super admin puede eliminar permanentemente
        return $user->hasRole('super admin');
    }
}
