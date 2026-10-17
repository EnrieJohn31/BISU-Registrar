<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\AuditOutcome;
use App\Enums\OfficeCode;
use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Office;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AuditEventController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $viewer */
        $viewer = $request->user();

        // null = campus-wide (administrators); otherwise only the viewer's offices.
        $officeIds = $viewer->officeIdsWithPermission(Permission::AuditView);

        $action = AuditAction::tryFrom($request->string('action')->toString());
        $outcome = AuditOutcome::tryFrom($request->string('outcome')->toString());
        $office = OfficeCode::tryFrom($request->string('office')->toString());
        $actor = trim($request->string('actor')->toString());
        $from = $this->date($request->string('from')->toString());
        $to = $this->date($request->string('to')->toString());

        $events = AuditEvent::query()
            ->with(['actor', 'office'])
            ->when($officeIds !== null, fn (Builder $query) => $query->whereIn('office_id', $officeIds ?? []))
            ->when($action, fn (Builder $query, AuditAction $action) => $query->where('action', $action->value))
            ->when($outcome, fn (Builder $query, AuditOutcome $outcome) => $query->where('outcome', $outcome->value))
            ->when($office, fn (Builder $query, OfficeCode $office) => $query->whereHas(
                'office',
                fn (Builder $query) => $query->where('code', $office->value),
            ))
            ->when($actor !== '', function (Builder $query) use ($actor): void {
                $like = '%'.addcslashes($actor, '%_\\').'%';
                $query->whereHas('actor', fn (Builder $query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($from, fn (Builder $query, CarbonImmutable $from) => $query->where('occurred_at', '>=', $from->startOfDay()))
            ->when($to, fn (Builder $query, CarbonImmutable $to) => $query->where('occurred_at', '<=', $to->endOfDay()))
            ->latest('occurred_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $subjectNames = User::query()
            ->whereIn('id', $events->getCollection()
                ->where('subject_type', 'user')
                ->pluck('subject_id')
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->all())
            ->pluck('name', 'id');

        $events->through(fn (AuditEvent $event): array => [
            'id' => $event->id,
            'occurred_at' => $event->occurred_at->toIso8601String(),
            'correlation_id' => $event->correlation_id,
            'action' => $event->action,
            'action_label' => AuditAction::tryFrom($event->action)?->label() ?? $event->action,
            'outcome' => $event->outcome->value,
            'actor_type' => $event->actor_type,
            'actor' => $event->actor === null ? null : [
                'id' => $event->actor->id,
                'name' => $event->actor->name,
                'email' => $event->actor->email,
            ],
            'office' => $event->office?->code->value,
            'subject_type' => $event->subject_type,
            'subject_id' => $event->subject_id,
            'subject_label' => $event->subject_type === 'user'
                ? ($subjectNames[(int) $event->subject_id] ?? null)
                : null,
            'reason' => $event->reason,
            'changes' => $event->changes,
            'context' => $event->context,
            'ip_address' => $event->ip_address,
        ]);

        return Inertia::render('admin/audit/Index', [
            'events' => $events,
            'filters' => [
                'action' => $action?->value,
                'outcome' => $outcome?->value,
                'office' => $office?->value,
                'actor' => $actor,
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'actionOptions' => array_map(fn (AuditAction $action): array => [
                'value' => $action->value,
                'label' => $action->label(),
            ], AuditAction::cases()),
            'officeOptions' => Office::query()
                ->when($officeIds !== null, fn (Builder $query) => $query->whereIn('id', $officeIds ?? []))
                ->orderBy('name')
                ->get()
                ->map(fn (Office $office): array => ['code' => $office->code->value, 'name' => $office->name])
                ->values()
                ->all(),
            'scope' => $officeIds === null ? 'all' : 'office',
        ]);
    }

    private function date(string $value): ?CarbonImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value) ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
