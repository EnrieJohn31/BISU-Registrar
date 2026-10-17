<?php

namespace App\Enums;

/**
 * Staff roles and their permissions (System_Plan Part 03, decision C09). Changing
 * this map changes what people can do: it goes through a reviewed pull request.
 * Clients are not a role; they act through a verified Student/Alumni ID link.
 */
enum Role: string
{
    case SystemAdmin = 'system_admin';
    case IntakeStaff = 'intake_staff';
    case RegistrarProcessor = 'registrar_processor';
    case RegistrarApprover = 'registrar_approver';
    case ReleaseOfficer = 'release_officer';
    case Cashier = 'cashier';
    case OfficeSupervisor = 'office_supervisor';
    case ReportExporter = 'report_exporter';
    case SupplierStaff = 'supplier_staff';
    case SupplierSupervisor = 'supplier_supervisor';

    public function label(): string
    {
        return match ($this) {
            self::SystemAdmin => 'System administrator',
            self::IntakeStaff => 'Intake staff',
            self::RegistrarProcessor => 'Registrar processor',
            self::RegistrarApprover => 'Registrar approver',
            self::ReleaseOfficer => 'Release officer',
            self::Cashier => 'Cashier',
            self::OfficeSupervisor => 'Office supervisor',
            self::ReportExporter => 'Report exporter',
            self::SupplierStaff => 'Supplier staff',
            self::SupplierSupervisor => 'Supplier supervisor',
        };
    }

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SystemAdmin => [
                Permission::UsersView,
                Permission::UsersManage,
                Permission::UsersDeactivate,
                Permission::RolesAssign,
                Permission::AuditView,
            ],
            self::IntakeStaff => [
                Permission::ClientsView,
                Permission::RequestsIntake,
                Permission::QueueOperate,
                Permission::RequestsView,
            ],
            self::RegistrarProcessor => [
                Permission::ClientsView,
                Permission::ClientsManage,
                Permission::ClientsImport,
                Permission::ClientLinksReview,
                Permission::RequestsIntake,
                Permission::QueueOperate,
                Permission::RequestsView,
                Permission::RequestsReview,
                Permission::RequestsProcess,
                Permission::AssessmentsEndorse,
                Permission::HoldsManage,
                Permission::DocumentsGenerate,
            ],
            self::RegistrarApprover => [
                Permission::ClientsView,
                Permission::RequestsView,
                Permission::DocumentsApprove,
            ],
            self::ReleaseOfficer => [
                Permission::ClientsView,
                Permission::QueueOperate,
                Permission::RequestsView,
                Permission::ReleasesRecord,
            ],
            self::Cashier => [
                Permission::QueueOperate,
                Permission::RequestsView,
                Permission::PaymentsRecord,
                Permission::PaymentsVerify,
                Permission::CollectionsView,
            ],
            self::OfficeSupervisor => [
                Permission::AuditView,
                Permission::ClientsImport,
                Permission::ServicesManage,
                Permission::WaiversApprove,
                Permission::FeesManage,
                Permission::QueueConfigure,
                Permission::RequestsView,
                Permission::RequestsAssign,
                Permission::HoldsManage,
                Permission::PaymentsAdjust,
                Permission::CollectionsView,
                Permission::TemplatesManage,
                Permission::ReportsView,
            ],
            self::ReportExporter => [
                Permission::ReportsExport,
                Permission::SupplierReportsExport,
            ],
            self::SupplierStaff => [
                Permission::SuppliersView,
                Permission::SuppliersManage,
                Permission::SupplierDeliveriesRecord,
                Permission::SupplierRatingsSubmit,
            ],
            self::SupplierSupervisor => [
                Permission::SuppliersView,
                Permission::SupplierRatingsConfirm,
                Permission::SuppliersChangeStatus,
                Permission::SupplierSettingsManage,
                Permission::SupplierReportsView,
            ],
        };
    }

    /**
     * Offices this role may be assigned in. An empty list means campus-wide
     * (the assignment has no office).
     *
     * @return list<OfficeCode>
     */
    public function allowedOffices(): array
    {
        return match ($this) {
            self::SystemAdmin => [],
            self::Cashier => [OfficeCode::Cashier],
            self::OfficeSupervisor, self::ReportExporter => [OfficeCode::Registrar, OfficeCode::Cashier],
            default => [OfficeCode::Registrar],
        };
    }

    public function isCampusWide(): bool
    {
        return $this->allowedOffices() === [];
    }

    public function isAllowedIn(?OfficeCode $office): bool
    {
        if ($this->isCampusWide()) {
            return $office === null;
        }

        return $office !== null && in_array($office, $this->allowedOffices(), true);
    }

    /**
     * Roles that handle money, approvals, releases, supervision or administration
     * must use confirmed two-factor authentication.
     */
    public function requiresTwoFactor(): bool
    {
        return match ($this) {
            self::IntakeStaff, self::RegistrarProcessor, self::SupplierStaff => false,
            default => true,
        };
    }
}
