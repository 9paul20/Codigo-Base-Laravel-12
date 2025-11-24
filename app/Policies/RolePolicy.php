<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view roles');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('create roles');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('update roles');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('delete roles');
    }

    /**
     * Determine whether the user can restore the role.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('restore roles');
    }

    /**
     * Determine whether the user can permanently delete the role.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('force delete roles');
    }
}
