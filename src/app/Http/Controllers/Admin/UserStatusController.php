<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Accounts\DeactivateUser;
use App\Actions\Accounts\ReactivateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReasonRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UserStatusController extends Controller
{
    public function deactivate(ReasonRequest $request, User $user, DeactivateUser $deactivate): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $deactivate->handle($actor, $user, $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account deactivated and signed out everywhere.')]);

        return to_route('admin.users.show', $user);
    }

    public function reactivate(ReasonRequest $request, User $user, ReactivateUser $reactivate): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $reactivate->handle($actor, $user, $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account reactivated.')]);

        return to_route('admin.users.show', $user);
    }
}
