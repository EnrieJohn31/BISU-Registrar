<?php

namespace App\Http\Controllers\Registrar;

use App\Actions\Clients\CorrectClientProfile;
use App\Actions\Clients\CreateClientProfile;
use App\Enums\ClientCategory;
use App\Enums\VerificationState;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Registrar\Concerns\PresentsClients;
use App\Http\Requests\Registrar\StoreClientProfileRequest;
use App\Http\Requests\Registrar\UpdateClientProfileRequest;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ClientProfileController extends Controller
{
    use PresentsClients;

    private const MINIMUM_SEARCH_LENGTH = 2;

    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());
        $category = ClientCategory::tryFrom($request->string('category')->toString());
        $state = VerificationState::tryFrom($request->string('state')->toString());

        $profiles = ClientProfile::query()
            ->with('currentIdentifiers')
            ->when(mb_strlen($search) >= self::MINIMUM_SEARCH_LENGTH, function (Builder $query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(function (Builder $query) use ($search, $like): void {
                    $query->where('display_name', 'like', $like);

                    // IDs are matched by their start, against the exact stored form.
                    if (preg_match('/\A[A-Za-z0-9-]+\z/', $search) === 1) {
                        $prefix = addcslashes(strtoupper($search), '%_\\').'%';
                        $query->orWhereHas('identifiers', fn (Builder $identifiers) => $identifiers->where('canonical_value', 'like', $prefix));
                    }
                });
            })
            ->when($category, fn (Builder $query, ClientCategory $category) => $query->where('category', $category->value))
            ->when($state, fn (Builder $query, VerificationState $state) => $query->where('verification_state', $state->value))
            ->orderBy('display_name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ClientProfile $profile): array => $this->clientSummary($profile));

        return Inertia::render('registrar/clients/Index', [
            'profiles' => $profiles,
            'filters' => [
                'search' => $search,
                'category' => $category?->value,
                'state' => $state?->value,
            ],
            'options' => $this->clientOptions(),
            'canCreate' => Gate::allows('create', ClientProfile::class),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('registrar/clients/Create', [
            'options' => $this->clientOptions(),
        ]);
    }

    public function store(StoreClientProfileRequest $request, CreateClientProfile $create): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $profile = $create->handle(
            $actor,
            $request->displayName(),
            $request->category(),
            $request->sourceReference(),
            $request->identifierType(),
            $request->identifierValue(),
            $request->reason(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client profile created.')]);

        return to_route('registrar.clients.show', $profile);
    }

    public function show(ClientProfile $clientProfile): Response
    {
        $clientProfile->load(['verifiedBy', 'createdBy', 'identifiers.addedBy', 'identifiers.retiredBy']);

        $revisions = $clientProfile->revisions()->with('actor')->limit(50)->get();

        return Inertia::render('registrar/clients/Show', [
            'profile' => $this->clientDetail($clientProfile),
            'revisions' => $revisions->map(fn ($revision): array => $this->revisionSummary($revision))->values()->all(),
            'options' => $this->clientOptions(),
            'can' => [
                'manage' => Gate::allows('manage', $clientProfile),
            ],
        ]);
    }

    public function update(UpdateClientProfileRequest $request, ClientProfile $clientProfile, CorrectClientProfile $correct): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $correct->handle(
            $actor,
            $clientProfile,
            $request->version(),
            $request->displayName(),
            $request->category(),
            $request->reason(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Correction saved.')]);

        return to_route('registrar.clients.show', $clientProfile);
    }
}
