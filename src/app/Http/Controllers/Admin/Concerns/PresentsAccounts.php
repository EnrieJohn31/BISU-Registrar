<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Enums\Role;
use App\Models\Office;
use App\Models\User;
use App\Models\UserRole;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

trait PresentsAccounts
{
    /**
     * @return array<string, mixed>
     */
    protected function userSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->isDeactivated() ? 'deactivated' : 'active',
            'is_staff' => $user->isStaff(),
            'two_factor_enabled' => $user->hasEnabledTwoFactorAuthentication(),
            'must_change_password' => $user->must_change_password,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'roles' => $user->activeRoleAssignments
                ->map(fn (UserRole $assignment): array => $this->assignmentSummary($assignment))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function assignmentSummary(UserRole $assignment): array
    {
        return [
            'id' => $assignment->id,
            'role' => $assignment->role->value,
            'label' => $assignment->role->label(),
            'office' => $assignment->office?->code->value,
            'office_name' => $assignment->office?->name,
        ];
    }

    /**
     * Role choices for forms: which offices each role may be assigned in.
     *
     * @return list<array<string, mixed>>
     */
    protected function roleOptions(): array
    {
        return array_map(fn (Role $role): array => [
            'value' => $role->value,
            'label' => $role->label(),
            'campus_wide' => $role->isCampusWide(),
            'offices' => array_map(fn ($office) => $office->value, $role->allowedOffices()),
            'requires_two_factor' => $role->requiresTwoFactor(),
        ], Role::cases());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function officeOptions(): array
    {
        return Office::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Office $office): array => [
                'id' => $office->id,
                'code' => $office->code->value,
                'name' => $office->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Show a temporary password exactly once. It is rendered directly instead
     * of flashed, so it is never stored in the session, and the response must
     * not be cached.
     */
    protected function temporaryPasswordResponse(
        Request $request,
        User $user,
        string $temporaryPassword,
        CarbonImmutable $expiresAt,
        string $context,
    ): Response {
        $response = Inertia::render('admin/users/TemporaryPassword', [
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'temporaryPassword' => $temporaryPassword,
            'expiresAt' => $expiresAt->toIso8601String(),
            'context' => $context,
        ])->toResponse($request);

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
