export type VerificationStateValue = 'unverified' | 'verified' | 'needs_review';

export type LabelledOption = { value: string; label: string };

export type ClientOptions = {
    categories: LabelledOption[];
    states: LabelledOption[];
    identifierTypes: LabelledOption[];
    identifierRule: string;
};

export type MaskedIdentifier = {
    id: number;
    type: string;
    type_label: string;
    masked: string;
};

export type ClientSummary = {
    id: number;
    display_name: string;
    category: string;
    category_label: string;
    verification_state: VerificationStateValue;
    verification_label: string;
    identifiers: MaskedIdentifier[];
    updated_at: string | null;
};

export type ClientIdentifierRow = {
    id: number;
    type: string;
    type_label: string;
    display_value: string;
    current: boolean;
    added_at: string;
    added_by: string;
    retired_at: string | null;
    retired_by: string | null;
    retire_reason: string | null;
};

export type ClientDetail = {
    id: number;
    display_name: string;
    category: string;
    category_label: string;
    verification_state: VerificationStateValue;
    verification_label: string;
    source_reference: string;
    verified_by: string | null;
    verified_at: string | null;
    created_by: string;
    created_at: string | null;
    updated_at: string | null;
    version: number;
    identifiers: ClientIdentifierRow[];
};

export type ClientRevision = {
    revision_number: number;
    action: string;
    action_label: string;
    actor: string;
    occurred_at: string;
    reason: string;
    changes: Record<string, { old: unknown; new: unknown }> | null;
};
