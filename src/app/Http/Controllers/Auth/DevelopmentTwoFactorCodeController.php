<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Accounts\DevelopmentTwoFactorEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Development-only (C22): emails the current two-factor code to the test inbox.
 * Every action refuses with 404 outside the local environment.
 */
class DevelopmentTwoFactorCodeController extends Controller
{
    /**
     * Sign-in challenge: the account that already passed the password step.
     */
    public function challenge(Request $request): JsonResponse
    {
        abort_unless(DevelopmentTwoFactorEmail::enabled(), 404);

        $user = User::query()->find($request->session()->get('login.id'));

        abort_unless($user instanceof User && $user->hasEnabledTwoFactorAuthentication() && ! $user->isDeactivated(), 404);

        DevelopmentTwoFactorEmail::send($user);

        return response()->json(['status' => 'sent']);
    }

    /**
     * Setup: the signed-in user's new secret that is not confirmed yet.
     */
    public function setup(Request $request): JsonResponse
    {
        abort_unless(DevelopmentTwoFactorEmail::enabled(), 404);

        /** @var User $user */
        $user = $request->user();

        abort_if($user->two_factor_secret === null || $user->two_factor_confirmed_at !== null, 404);

        DevelopmentTwoFactorEmail::send($user);

        return response()->json(['status' => 'sent']);
    }
}
