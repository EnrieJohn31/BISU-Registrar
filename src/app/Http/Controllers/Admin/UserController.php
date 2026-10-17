<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Accounts\ProvisionStaffUser;
use App\Actions\Accounts\UpdateUserDetails;
use App\Enums\AuditAction;
use App\Enums\OfficeCode;
use App\Enums\Role;
use App\Http\Controllers\Admin\Concerns\PresentsAccounts;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AuditEvent;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserController extends Controller
{
    use PresentsAccounts;

    private const STATUSES = ['active', 'deactivated', 'staff', 'clients'];

    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->toString());
        $role = Role::tryFrom($request->string('role')->toString());
        $office = OfficeCode::tryFrom($request->string('office')->toString());
        $status = in_array($request->string('status')->toString(), self::STATUSES, true)
            ? $request->string('status')->toString()
            : null;

        $users = User::query()
            ->with('activeRoleAssignments.office')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn (Builder $query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($role !== null || $office !== null, fn (Builder $query) => $query->whereHas(
                'activeRoleAssignments',
                fn (Builder $query) => $query
                    ->when($role, fn (Builder $query, Role $role) => $query->where('role', $role->value))
                    ->when($office, fn (Builder $query, OfficeCode $office) => $query->whereHas(
                        'office',
                        fn (Builder $query) => $query->where('code', $office->value),
                    )),
            ))
            ->when($status === 'active', fn (Builder $query) => $query->whereNull('deactivated_at'))
            ->when($status === 'deactivated', fn (Builder $query) => $query->whereNotNull('deactivated_at'))
            ->when($status === 'staff', fn (Builder $query) => $query->whereHas('activeRoleAssignments'))
            ->when($status === 'clients', fn (Builder $query) => $query->whereDoesntHave('activeRoleAssignments'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user): array => $this->userSummary($user));

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role?->value,
                'office' => $office?->value,
                'status' => $status,
            ],
            'roleOptions' => $this->roleOptions(),
            'officeOptions' => $this->officeOptions(),
            'canCreate' => Gate::allows('create', User::class),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/Create', [
            'roleOptions' => $this->roleOptions(),
            'officeOptions' => $this->officeOptions(),
        ]);
    }

    public function store(StoreStaffUserRequest $request, ProvisionStaffUser $provision): HttpResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $result = $provision->handle(
            $actor,
            (string) $request->validated('name'),
            (string) $request->validated('email'),
            $request->office(),
            $request->roles(),
            $request->reason(),
        );

        return $this->temporaryPasswordResponse(
            $request,
            $result['user'],
            $result['temporaryPassword'],
            $result['expiresAt'],
            'provisioned',
        );
    }

    public function show(User $user): Response
    {
        $user->load([
            'activeRoleAssignments.office',
            'roleAssignments' => fn ($query) => $query->with(['office', 'assignedBy', 'revokedBy'])->orderByDesc('assigned_at'),
        ]);

        $events = AuditEvent::query()
            ->with('actor')
            ->where('subject_type', $user->getMorphClass())
            ->where('subject_id', (string) $user->id)
            ->latest('occurred_at')
            ->latest('id')
            ->limit(15)
            ->get()
            ->map(fn (AuditEvent $event): array => [
                'id' => $event->id,
                'occurred_at' => $event->occurred_at->toIso8601String(),
                'action' => $event->action,
                'action_label' => AuditAction::tryFrom($event->action)?->label() ?? $event->action,
                'outcome' => $event->outcome->value,
                'actor' => $event->actor?->name,
                'reason' => $event->reason,
            ]);

        return Inertia::render('admin/users/Show', [
            'user' => [
                ...$this->userSummary($user),
                'created_at' => $user->created_at?->toIso8601String(),
                'deactivated_at' => $user->deactivated_at?->toIso8601String(),
                'deactivation_reason' => $user->deactivation_reason,
                'temporary_password_expires_at' => $user->temporary_password_expires_at?->toIso8601String(),
                'requires_two_factor' => $user->requiresTwoFactor(),
            ],
            'history' => $user->roleAssignments->map(fn (UserRole $assignment): array => [
                ...$this->assignmentSummary($assignment),
                'active' => $assignment->isActive(),
                'assigned_at' => $assignment->assigned_at->toIso8601String(),
                'assigned_by' => $assignment->assignedBy->name ?? 'System',
                'assignment_reason' => $assignment->assignment_reason,
                'revoked_at' => $assignment->revoked_at?->toIso8601String(),
                'revoked_by' => $assignment->revokedBy?->name,
                'revoke_reason' => $assignment->revoke_reason,
            ])->values()->all(),
            'events' => $events,
            'roleOptions' => $this->roleOptions(),
            'officeOptions' => $this->officeOptions(),
            'can' => [
                'update' => Gate::allows('update', $user),
                'manageRoles' => Gate::allows('manageRoles', $user),
                'changeStatus' => Gate::allows('changeStatus', $user),
                'manageCredentials' => Gate::allows('manageCredentials', $user),
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserDetails $update): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $update->handle(
            $actor,
            $user,
            (string) $request->validated('name'),
            (string) $request->validated('email'),
            $request->reason(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Account details updated.')]);

        return to_route('admin.users.show', $user);
    }
}
