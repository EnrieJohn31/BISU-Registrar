<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

/**
 * Account administration. Nobody administers their own account here: profile
 * and security settings cover a person's own details.
 */
class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::UsersView);
    }

    public function view(User $actor, User $user): bool
    {
        return $actor->hasPermission(Permission::UsersView);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::UsersManage);
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->isNot($user) && $actor->hasPermission(Permission::UsersManage);
    }

    public function manageRoles(User $actor, User $user): bool
    {
        return $actor->isNot($user) && $actor->hasPermission(Permission::RolesAssign);
    }

    public function changeStatus(User $actor, User $user): bool
    {
        return $actor->isNot($user) && $actor->hasPermission(Permission::UsersDeactivate);
    }

    public function manageCredentials(User $actor, User $user): bool
    {
        return $actor->isNot($user) && $actor->hasPermission(Permission::UsersManage);
    }
}
