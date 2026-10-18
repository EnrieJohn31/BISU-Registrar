<?php

namespace App\Http\Controllers\Registrar;

use App\Actions\Clients\FlagClientProfileForReview;
use App\Actions\Clients\VerifyClientProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registrar\ClientChangeRequest;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ClientVerificationController extends Controller
{
    public function verify(ClientChangeRequest $request, ClientProfile $clientProfile, VerifyClientProfile $verify): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $verify->handle($actor, $clientProfile, $request->version(), $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile marked as verified.')]);

        return to_route('registrar.clients.show', $clientProfile);
    }

    public function flag(ClientChangeRequest $request, ClientProfile $clientProfile, FlagClientProfileForReview $flag): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $flag->handle($actor, $clientProfile, $request->version(), $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile flagged for review.')]);

        return to_route('registrar.clients.show', $clientProfile);
    }
}
