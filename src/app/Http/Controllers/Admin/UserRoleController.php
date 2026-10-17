<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Accounts\AssignRole;
use App\Actions\Accounts\RevokeRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\ReasonRequest;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UserRoleController extends Controller
{
    public function store(AssignRoleRequest $request, User $user, AssignRole $assign): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $assign->handle($actor, $user, $request->role(), $request->office(), $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':role assigned.', ['role' => $request->role()->label()])]);

        return to_route('admin.users.show', $user);
    }

    public function destroy(ReasonRequest $request, User $user, UserRole $roleAssignment, RevokeRole $revoke): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $revoke->handle($actor, $roleAssignment, $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':role revoked.', ['role' => $roleAssignment->role->label()])]);

        return to_route('admin.users.show', $user);
    }
}
