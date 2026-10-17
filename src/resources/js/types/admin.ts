export type AccountStatus = 'active' | 'deactivated';

export type AssignmentSummary = {
    id: number;
    role: string;
    label: string;
    office: string | null;
    office_name: string | null;
};

export type UserSummary = {
    id: number;
    name: string;
    email: string;
    status: AccountStatus;
    is_staff: boolean;
    two_factor_enabled: boolean;
    must_change_password: boolean;
    last_login_at: string | null;
    roles: AssignmentSummary[];
};

export type RoleOption = {
    value: string;
    label: string;
    campus_wide: boolean;
    offices: string[];
    requires_two_factor: boolean;
};

export type OfficeOption = {
    id: number;
    code: string;
    name: string;
};

export type RoleHistoryEntry = AssignmentSummary & {
    active: boolean;
    assigned_at: string;
    assigned_by: string;
    assignment_reason: string;
    revoked_at: string | null;
    revoked_by: string | null;
    revoke_reason: string | null;
};

export type AuditOutcome = 'succeeded' | 'denied';

export type AuditEventSummary = {
    id: number;
    occurred_at: string;
    action: string;
    action_label: string;
    outcome: AuditOutcome;
    actor: string | null;
    reason: string | null;
};

export type AuditEventDetail = {
    id: number;
    occurred_at: string;
    correlation_id: string;
    action: string;
    action_label: string;
    outcome: AuditOutcome;
    actor_type: 'user' | 'system';
    actor: { id: number; name: string; email: string } | null;
    office: string | null;
    subject_type: string | null;
    subject_id: string | null;
    subject_label: string | null;
    reason: string | null;
    changes: Record<string, { old: unknown; new: unknown }> | null;
    context: Record<string, unknown> | null;
    ip_address: string | null;
};
