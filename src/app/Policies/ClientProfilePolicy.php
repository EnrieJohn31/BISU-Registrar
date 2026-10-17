<?php

namespace App\Policies;

use App\Enums\OfficeCode;
use App\Enums\Permission;
use App\Models\ClientProfile;
use App\Models\Office;
use App\Models\User;

/**
 * Client records belong to the Registrar's Office. Every check requires the
 * permission in the Registrar office, so a Cashier-only assignment never reaches
 * them even if the shared role catalogue lists a client permission for it.
 * System administrators manage accounts; they hold no client permissions.
 */
class ClientProfilePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission(Permission::ClientsView, $this->registrar());
    }

    public function view(User $actor, ClientProfile $profile): bool
    {
        return $actor->hasPermission(Permission::ClientsView, $profile->office);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission(Permission::ClientsManage, $this->registrar());
    }

    /**
     * Correct details, verify, flag, and add or retire IDs.
     */
    public function manage(User $actor, ClientProfile $profile): bool
    {
        return $actor->hasPermission(Permission::ClientsManage, $profile->office);
    }

    private function registrar(): Office
    {
        return Office::forCode(OfficeCode::Registrar);
    }
}
