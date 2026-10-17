<?php

namespace App\Enums;

/**
 * Audit event names. Later parts add their own cases (System_Plan Part 03,
 * required audit events).
 */
enum AuditAction: string
{
    case UserProvisioned = 'user.provisioned';
    case UserUpdated = 'user.updated';
    case UserDeactivated = 'user.deactivated';
    case UserReactivated = 'user.reactivated';
    case UserTemporaryPasswordIssued = 'user.temporary_password_issued';
    case UserTwoFactorReset = 'user.two_factor_reset';
    case UserPasswordChanged = 'user.password_changed';
    case RoleAssigned = 'role.assigned';
    case RoleRevoked = 'role.revoked';
    case ClientCreated = 'client.created';
    case ClientCorrected = 'client.corrected';
    case ClientVerified = 'client.verified';
    case ClientFlaggedForReview = 'client.flagged_for_review';
    case ClientIdentifierAdded = 'client.identifier_added';
    case ClientIdentifierRetired = 'client.identifier_retired';
    case AuthDenied = 'auth.denied';

    public function label(): string
    {
        return match ($this) {
            self::UserProvisioned => 'Staff account created',
            self::UserUpdated => 'Account details changed',
            self::UserDeactivated => 'Account deactivated',
            self::UserReactivated => 'Account reactivated',
            self::UserTemporaryPasswordIssued => 'Temporary password issued',
            self::UserTwoFactorReset => 'Two-factor reset',
            self::UserPasswordChanged => 'Password changed',
            self::RoleAssigned => 'Role assigned',
            self::RoleRevoked => 'Role revoked',
            self::ClientCreated => 'Client profile created',
            self::ClientCorrected => 'Client profile corrected',
            self::ClientVerified => 'Client profile verified',
            self::ClientFlaggedForReview => 'Client profile flagged for review',
            self::ClientIdentifierAdded => 'Client ID added',
            self::ClientIdentifierRetired => 'Client ID retired',
            self::AuthDenied => 'Access denied',
        };
    }
}
