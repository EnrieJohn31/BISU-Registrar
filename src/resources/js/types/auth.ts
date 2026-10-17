import type { Permission } from './permissions';

// Shared with every page through an explicit server-side allow-list.
export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    must_change_password?: boolean;
    [key: string]: unknown;
};

export type RoleSummary = {
    role: string;
    label: string;
    office: string | null;
    office_name: string | null;
};

export type Auth = {
    user: User;
    permissions: Permission[];
    roles: RoleSummary[];
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
