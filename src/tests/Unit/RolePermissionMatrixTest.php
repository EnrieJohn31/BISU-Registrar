<?php

namespace Tests\Unit;

use App\Enums\OfficeCode;
use App\Enums\Permission;
use App\Enums\Role;
use PHPUnit\Framework\TestCase;

/**
 * The approved role → permission matrix (System_Plan Part 03, C09). If this test
 * fails, the code no longer matches what the offices approved: update the plan
 * and get the change reviewed, do not just edit the expectation.
 */
class RolePermissionMatrixTest extends TestCase
{
    /**
     * @return array<string, list<string>>
     */
    private function approvedMatrix(): array
    {
        return [
            'system_admin' => ['users.view', 'users.manage', 'users.deactivate', 'roles.assign', 'audit.view'],
            'intake_staff' => ['clients.view', 'requests.intake', 'queue.operate', 'requests.view'],
            'registrar_processor' => [
                'clients.view', 'clients.manage', 'clients.import', 'client_links.review', 'requests.intake',
                'queue.operate', 'requests.view', 'requests.review', 'requests.process', 'assessments.endorse',
                'holds.manage', 'documents.generate',
            ],
            'registrar_approver' => ['clients.view', 'requests.view', 'documents.approve'],
            'release_officer' => ['clients.view', 'queue.operate', 'requests.view', 'releases.record'],
            'cashier' => ['queue.operate', 'requests.view', 'payments.record', 'payments.verify', 'collections.view'],
            'office_supervisor' => [
                'audit.view', 'clients.import', 'services.manage', 'waivers.approve', 'fees.manage', 'queue.configure',
                'requests.view', 'requests.assign', 'holds.manage', 'payments.adjust', 'collections.view',
                'templates.manage', 'reports.view',
            ],
            'report_exporter' => ['reports.export', 'supplier_reports.export'],
            'supplier_staff' => ['suppliers.view', 'suppliers.manage', 'supplier_deliveries.record', 'supplier_ratings.submit'],
            'supplier_supervisor' => [
                'suppliers.view', 'supplier_ratings.confirm', 'suppliers.change_status', 'supplier_settings.manage',
                'supplier_reports.view',
            ],
        ];
    }

    public function test_each_role_holds_exactly_the_approved_permissions(): void
    {
        $actual = [];

        foreach (Role::cases() as $role) {
            $actual[$role->value] = array_map(fn (Permission $permission): string => $permission->value, $role->permissions());
        }

        $expected = $this->approvedMatrix();

        $this->assertSame(array_keys($expected), array_keys($actual));

        foreach ($expected as $role => $permissions) {
            $this->assertEqualsCanonicalizing($permissions, $actual[$role], "Permissions for {$role} differ from the approved matrix.");
        }
    }

    public function test_every_permission_is_held_by_at_least_one_role(): void
    {
        $held = [];

        foreach (Role::cases() as $role) {
            foreach ($role->permissions() as $permission) {
                $held[$permission->value] = true;
            }
        }

        foreach (Permission::cases() as $permission) {
            $this->assertArrayHasKey($permission->value, $held, "{$permission->value} is not held by any role.");
        }
    }

    public function test_the_system_administrator_holds_no_business_permissions(): void
    {
        $accountPermissions = [Permission::UsersView, Permission::UsersManage, Permission::UsersDeactivate, Permission::RolesAssign, Permission::AuditView];

        $this->assertEqualsCanonicalizing($accountPermissions, Role::SystemAdmin->permissions());
    }

    public function test_only_the_system_administrator_is_campus_wide(): void
    {
        foreach (Role::cases() as $role) {
            $this->assertSame($role === Role::SystemAdmin, $role->isCampusWide(), $role->value);
        }

        $this->assertTrue(Role::SystemAdmin->isAllowedIn(null));
        $this->assertFalse(Role::SystemAdmin->isAllowedIn(OfficeCode::Registrar));
        $this->assertTrue(Role::Cashier->isAllowedIn(OfficeCode::Cashier));
        $this->assertFalse(Role::Cashier->isAllowedIn(OfficeCode::Registrar));
        $this->assertFalse(Role::RegistrarProcessor->isAllowedIn(null));
        $this->assertTrue(Role::OfficeSupervisor->isAllowedIn(OfficeCode::Cashier));
    }

    public function test_roles_that_require_two_factor(): void
    {
        $requiring = array_values(array_filter(Role::cases(), fn (Role $role): bool => $role->requiresTwoFactor()));

        $this->assertEqualsCanonicalizing([
            Role::SystemAdmin,
            Role::RegistrarApprover,
            Role::ReleaseOfficer,
            Role::Cashier,
            Role::OfficeSupervisor,
            Role::ReportExporter,
            Role::SupplierSupervisor,
        ], $requiring);
    }
}
