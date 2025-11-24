<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any permissions.
     */
    public function viewAny(User $user): bool
    {
        // Allow admins or super admins to bypass permission checks
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view permissions');
    }

    /**
     * Determine whether the user can view the permission.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('view permissions');
    }

    /**
     * Determine whether the user can create permissions.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('create permissions');
    }

    /**
     * Determine whether the user can update the permission.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('update permissions');
    }

    /**
     * Determine whether the user can delete the permission.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('delete permissions');
    }

    /**
     * Determine whether the user can restore the permission.
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('restore permissions');
    }

    /**
     * Determine whether the user can permanently delete the permission.
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->hasRole('admin') || $user->hasRole('super admin') || $user->hasPermissionTo('force delete permissions');
    }
}
