<?php

namespace App\Actions\Accounts;

use App\Enums\AuditAction;
use App\Enums\Role;
use App\Models\Office;
use App\Models\User;
use App\Models\UserRole;
use App\Support\Audit\AuditRecorder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignRole
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Assign a role. A null actor means a system action (the first-administrator
     * command).
     *
     * @throws ValidationException
     */
    public function handle(?User $actor, User $target, Role $role, ?Office $office, string $reason): UserRole
    {
        if ($actor !== null && $actor->is($target)) {
            throw ValidationException::withMessages(['role' => __('You cannot change your own roles.')]);
        }

        if ($target->isDeactivated()) {
            throw ValidationException::withMessages(['role' => __('Reactivate the account before assigning roles.')]);
        }

        if (! $role->isAllowedIn($office?->code)) {
            throw ValidationException::withMessages([
                'office_id' => $role->isCampusWide()
                    ? __(':role is campus-wide and is assigned without an office.', ['role' => $role->label()])
                    : __(':role cannot be assigned in this office.', ['role' => $role->label()]),
            ]);
        }

        return DB::transaction(function () use ($actor, $target, $role, $office, $reason): UserRole {
            $alreadyHeld = $target->roleAssignments()
                ->whereNull('revoked_at')
                ->where('role', $role->value)
                ->when(
                    $office,
                    fn ($query, Office $office) => $query->where('office_id', $office->id),
                    fn ($query) => $query->whereNull('office_id'),
                )
                ->exists();

            if ($alreadyHeld) {
                throw ValidationException::withMessages(['role' => __('The user already holds this role in this office.')]);
            }

            try {
                $assignment = $target->roleAssignments()->create([
                    'role' => $role,
                    'office_id' => $office?->id,
                    'assigned_by_id' => $actor?->id,
                    'assigned_at' => now(),
                    'assignment_reason' => $reason,
                ]);
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['role' => __('The user already holds this role in this office.')]);
            }

            $this->audit->record(
                AuditAction::RoleAssigned,
                subject: $target,
                reason: $reason,
                context: [
                    'role' => $role->value,
                    'office' => $office?->code->value,
                    'assignment_id' => $assignment->id,
                ],
                office: $office,
                actor: $actor,
                system: $actor === null,
            );

            $target->forgetRoles();

            return $assignment;
        });
    }
}
