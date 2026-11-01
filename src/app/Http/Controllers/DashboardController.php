<?php

namespace App\Http\Controllers;

use App\Enums\VerificationState;
use App\Models\AuditEvent;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Shortcuts follow the same policies as the routes they open, so a tile
     * never leads to a page the viewer would be refused.
     */
    public function __invoke(): Response
    {
        $canViewClients = Gate::allows('viewAny', ClientProfile::class);

        return Inertia::render('Dashboard', [
            'access' => [
                'clients' => $canViewClients,
                'createClient' => Gate::allows('create', ClientProfile::class),
                'users' => Gate::allows('viewAny', User::class),
                'createUser' => Gate::allows('create', User::class),
                'audit' => Gate::allows('viewAny', AuditEvent::class),
            ],
            'clientCounts' => $canViewClients ? $this->clientCounts() : null,
        ]);
    }

    /**
     * Totals only: the dashboard never lists or names a client.
     *
     * @return array{total: int, verified: int, unverified: int, needs_review: int, updated_this_week: int}
     */
    private function clientCounts(): array
    {
        /** @var array<string, int> $byState */
        $byState = ClientProfile::query()
            ->toBase()
            ->selectRaw('verification_state, count(*) as aggregate')
            ->groupBy('verification_state')
            ->pluck('aggregate', 'verification_state')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();

        return [
            'total' => array_sum($byState),
            'verified' => $byState[VerificationState::Verified->value] ?? 0,
            'unverified' => $byState[VerificationState::Unverified->value] ?? 0,
            'needs_review' => $byState[VerificationState::NeedsReview->value] ?? 0,
            'updated_this_week' => ClientProfile::query()->where('updated_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
