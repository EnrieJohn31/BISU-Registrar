<?php

namespace App\Enums;

/**
 * The full permission catalogue (System_Plan Part 03). Each part enforces its own
 * permissions when it is built; keep resources/js/types/permissions.ts in sync.
 */
enum Permission: string
{
    // Part 03: accounts and audit
    case UsersView = 'users.view';
    case UsersManage = 'users.manage';
    case UsersDeactivate = 'users.deactivate';
    case RolesAssign = 'roles.assign';
    case AuditView = 'audit.view';

    // Part 04: client records
    case ClientsView = 'clients.view';
    case ClientsManage = 'clients.manage';
    case ClientsImport = 'clients.import';
    case ClientLinksReview = 'client_links.review';

    // Part 05: services and fees
    case ServicesManage = 'services.manage';
    case WaiversApprove = 'waivers.approve';
    case FeesManage = 'fees.manage';

    // Part 06: intake
    case RequestsIntake = 'requests.intake';

    // Part 07: queues
    case QueueOperate = 'queue.operate';
    case QueueConfigure = 'queue.configure';

    // Part 08: Registrar workspace
    case RequestsView = 'requests.view';
    case RequestsReview = 'requests.review';
    case RequestsProcess = 'requests.process';
    case AssessmentsEndorse = 'assessments.endorse';
    case RequestsAssign = 'requests.assign';
    case HoldsManage = 'holds.manage';

    // Part 09: Cashier
    case PaymentsRecord = 'payments.record';
    case PaymentsVerify = 'payments.verify';
    case PaymentsAdjust = 'payments.adjust';
    case CollectionsView = 'collections.view';

    // Part 10: documents
    case TemplatesManage = 'templates.manage';
    case DocumentsGenerate = 'documents.generate';
    case DocumentsApprove = 'documents.approve';

    // Part 11: release
    case ReleasesRecord = 'releases.record';

    // Part 12: reports
    case ReportsView = 'reports.view';
    case ReportsExport = 'reports.export';

    // Part 16: suppliers
    case SuppliersView = 'suppliers.view';
    case SuppliersManage = 'suppliers.manage';
    case SupplierDeliveriesRecord = 'supplier_deliveries.record';
    case SupplierRatingsSubmit = 'supplier_ratings.submit';
    case SupplierRatingsConfirm = 'supplier_ratings.confirm';
    case SuppliersChangeStatus = 'suppliers.change_status';
    case SupplierSettingsManage = 'supplier_settings.manage';
    case SupplierReportsView = 'supplier_reports.view';
    case SupplierReportsExport = 'supplier_reports.export';
}
