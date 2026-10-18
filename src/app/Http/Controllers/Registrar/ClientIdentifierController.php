<?php

namespace App\Http\Controllers\Registrar;

use App\Actions\Clients\AddClientIdentifier;
use App\Actions\Clients\RetireClientIdentifier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registrar\AddClientIdentifierRequest;
use App\Http\Requests\Registrar\ClientChangeRequest;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ClientIdentifierController extends Controller
{
    public function store(AddClientIdentifierRequest $request, ClientProfile $clientProfile, AddClientIdentifier $add): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $add->handle(
            $actor,
            $clientProfile,
            $request->version(),
            $request->identifierType(),
            $request->identifierValue(),
            $request->reason(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('ID added.')]);

        return to_route('registrar.clients.show', $clientProfile);
    }

    public function destroy(
        ClientChangeRequest $request,
        ClientProfile $clientProfile,
        ClientIdentifier $clientIdentifier,
        RetireClientIdentifier $retire,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();

        $retire->handle($actor, $clientProfile, $clientIdentifier, $request->version(), $request->reason());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('ID retired. It stays in the history and cannot be reused.')]);

        return to_route('registrar.clients.show', $clientProfile);
    }
}
