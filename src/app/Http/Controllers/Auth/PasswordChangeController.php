<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Accounts\CompletePasswordChange;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordChangeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordChangeController extends Controller
{
    /**
     * The forced first sign-in step for accounts with a temporary password.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->must_change_password) {
            return redirect()->intended(config()->string('fortify.home', '/dashboard'));
        }

        return Inertia::render('auth/ChangePassword', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'expiresAt' => $user->temporary_password_expires_at?->toIso8601String(),
        ]);
    }

    public function update(PasswordChangeRequest $request, CompletePasswordChange $complete): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->must_change_password) {
            $complete->handle($user, (string) $request->validated('password'));

            Inertia::flash('toast', ['type' => 'success', 'message' => __('Password set. Welcome to BISU Registrar.')]);
        }

        return redirect()->intended(config()->string('fortify.home', '/dashboard'));
    }
}
