<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Accounts\IssueTemporaryPassword;
use App\Actions\Accounts\ResetTwoFactor;
use App\Http\Controllers\Admin\Concerns\PresentsAccounts;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReasonRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class UserCredentialController extends Controller
{
    use PresentsAccounts;

    public function temporaryPassword(ReasonRequest $request, User $user, IssueTemporaryPassword $issue): Response
    {
        /** @var User $actor */
        $actor = $request->user();

        $result = $issue->handle($actor, $user, $request->reason());

        return $this->temporaryPasswordResponse($request, $user, $result['temporaryPassword'], $result['expiresAt'], 'reissued');
    }

    public function resetTwoFactor(ReasonRequest $request, User $user, ResetTwoFactor $reset): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $reset->handle($actor, $user, $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Two-factor authentication was reset.')]);

        return to_route('admin.users.show', $user);
    }
}
