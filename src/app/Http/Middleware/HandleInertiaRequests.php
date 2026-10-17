<?php

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => $user instanceof User ? $this->authProps($user) : [
                'user' => null,
                'permissions' => [],
                'roles' => [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * An explicit allow-list: new columns on users are never sent to the
     * browser by accident. Permissions and roles only show or hide UI; the
     * server enforces every rule.
     *
     * @return array<string, mixed>
     */
    private function authProps(User $user): array
    {
        $user->loadMissing('activeRoleAssignments.office');

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'two_factor_enabled' => $user->hasEnabledTwoFactorAuthentication(),
                'must_change_password' => $user->must_change_password,
            ],
            'permissions' => array_map(fn (Permission $permission): string => $permission->value, $user->permissions()),
            'roles' => $user->isDeactivated() ? [] : $user->activeRoleAssignments
                ->map(fn (UserRole $assignment): array => [
                    'role' => $assignment->role->value,
                    'label' => $assignment->role->label(),
                    'office' => $assignment->office?->code->value,
                    'office_name' => $assignment->office?->name,
                ])
                ->values()
                ->all(),
        ];
    }
}
