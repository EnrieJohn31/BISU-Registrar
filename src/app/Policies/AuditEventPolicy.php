<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class AuditEventPolicy
{
    /**
     * Which events are visible is scoped by office in the query: campus-wide
     * holders see everything, office supervisors see their own office.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::AuditView);
    }
}
