<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;

class StatusPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Usuarios autenticados pueden ver la lista de status
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view statuses');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Status $status): bool
    {
        // Usuarios autenticados pueden ver status individuales
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view statuses');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo usuarios con permisos administrativos pueden crear status
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('create statuses');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Status $status): bool
    {
        // Solo usuarios con permisos administrativos pueden actualizar status
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('edit statuses');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Status $status): bool
    {
        // Solo usuarios con permisos administrativos pueden eliminar status
        if (!$user->hasRole('admin') && !$user->hasRole('super admin') && !$user->hasPermissionTo('delete statuses')) {
            return false;
        }

        // No permitir eliminar status que están siendo usados por usuarios
        if ($status->user()->exists()) {
            return false;
        }

        // No permitir eliminar status del sistema (si los hay)
        $systemStatuses = ['active', 'inactive', 'suspended'];
        if (in_array(strtolower($status->nombre), $systemStatuses)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Status $status): bool
    {
        // Solo super admin puede restaurar status (si se implementa soft delete)
        return $user->hasRole('super admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Status $status): bool
    {
        // Solo super admin puede eliminar permanentemente
        return $user->hasRole('super admin');
    }
}