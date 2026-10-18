<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientIdentifierController from '@/actions/App/Http/Controllers/Registrar/ClientIdentifierController';
import ClientVerificationController from '@/actions/App/Http/Controllers/Registrar/ClientVerificationController';
import ReasonDialog from '@/components/admin/ReasonDialog.vue';
import AddIdentifierDialog from '@/components/clients/AddIdentifierDialog.vue';
import CorrectClientDialog from '@/components/clients/CorrectClientDialog.vue';
import VerificationBadge from '@/components/clients/VerificationBadge.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDateTime } from '@/lib/format';
import { index, show } from '@/routes/registrar/clients';
import type {
    ClientDetail,
    ClientOptions,
    ClientRevision,
    Paginated,
} from '@/types';

const props = defineProps<{
    profile: ClientDetail;
    revisions: Paginated<ClientRevision>;
    options: ClientOptions;
    can: { manage: boolean };
}>();

defineOptions({
    layout: (props: { profile: ClientDetail }) => ({
        breadcrumbs: [
            { title: 'Client records', href: index() },
            { title: props.profile.display_name, href: show(props.profile.id) },
        ],
    }),
});

const labelFor = (
    options: { value: string; label: string }[],
    value: unknown,
) => options.find((option) => option.value === value)?.label ?? String(value);

const fieldLabels: Record<string, string> = {
    display_name: 'Name',
    category: 'Category',
    source_reference: 'Source',
    verification_state: 'Verification',
    identifier: 'ID',
};

const formatValue = (field: string, value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'none';
    }

    if (field === 'category') {
        return labelFor(props.options.categories, value);
    }

    if (field === 'verification_state') {
        return labelFor(props.options.states, value);
    }

    if (field === 'identifier' && typeof value === 'object') {
        const identifier = value as { type: string; value: string };

        return `${labelFor(props.options.identifierTypes, identifier.type)} ${identifier.value}`;
    }

    return String(value);
};

const currentIdentifiers = computed(() =>
    props.profile.identifiers.filter((identifier) => identifier.current),
);
</script>

<template>
    <Head :title="profile.display_name" />

    <div class="flex max-w-5xl flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 space-y-2">
                <Heading
                    :title="profile.display_name"
                    :description="profile.category_label"
                />
                <div class="-mt-6 flex flex-wrap gap-2">
                    <VerificationBadge
                        :state="profile.verification_state"
                        :label="profile.verification_label"
                    />
                    <Badge
                        v-for="identifier in currentIdentifiers"
                        :key="identifier.id"
                        variant="secondary"
                        class="font-mono"
                    >
                        {{ identifier.type_label }}
                        {{ identifier.display_value }}
                    </Badge>
                </div>
            </div>

            <div v-if="can.manage" class="flex flex-wrap gap-2">
                <CorrectClientDialog
                    :profile="profile"
                    :categories="options.categories"
                />
                <AddIdentifierDialog
                    :profile-id="profile.id"
                    :version="profile.version"
                    :identifier-types="options.identifierTypes"
                    :identifier-rule="options.identifierRule"
                />
                <ReasonDialog
                    v-if="profile.verification_state !== 'verified'"
                    title="Verify this client"
                    description="Confirm that the name and ID match a source you checked. Say which source in the reason. Do not paste ID documents or other personal details."
                    confirm-label="Verify"
                    reason-placeholder="For example: checked against the Registrar master list, sheet 12"
                    :action="ClientVerificationController.verify(profile.id)"
                    :data="{ version: profile.version }"
                />
                <ReasonDialog
                    v-if="profile.verification_state !== 'needs_review'"
                    title="Flag for review"
                    description="Use for a similar name, a name change, a possible duplicate or a conflicting ID. Nothing is merged or changed; the profile is marked so it is checked."
                    confirm-label="Flag for review"
                    reason-placeholder="What needs to be checked"
                    :action="ClientVerificationController.flag(profile.id)"
                    :data="{ version: profile.version }"
                />
            </div>
        </div>

        <Alert v-if="!can.manage">
            <AlertTitle>View only</AlertTitle>
            <AlertDescription>
                Your role can look up client records but not change them.
            </AlertDescription>
        </Alert>

        <Alert v-if="profile.verification_state === 'needs_review'">
            <AlertTitle>Needs review</AlertTitle>
            <AlertDescription>
                This profile was flagged, or its details were corrected after it
                was verified. Check it against the source, then verify it.
            </AlertDescription>
        </Alert>

        <Card>
            <CardHeader>
                <CardTitle>Details</CardTitle>
                <CardDescription>
                    Minimum information only. Version {{ profile.version }}.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted-foreground">Source</dt>
                        <dd class="break-words">
                            {{ profile.source_reference }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Created</dt>
                        <dd>
                            {{ formatDateTime(profile.created_at) }} by
                            {{ profile.created_by }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Verification</dt>
                        <dd>
                            <template v-if="profile.verified_at">
                                Verified
                                {{ formatDateTime(profile.verified_at) }} by
                                {{ profile.verified_by }}
                            </template>
                            <template v-else>
                                {{ profile.verification_label }}
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Last updated</dt>
                        <dd>{{ formatDateTime(profile.updated_at) }}</dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>IDs</CardTitle>
                <CardDescription>
                    Retired IDs stay here as history and can never be reused for
                    another person.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Added</TableHead>
                            <TableHead v-if="can.manage" class="text-right">
                                Action
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="identifier in profile.identifiers"
                            :key="identifier.id"
                        >
                            <TableCell class="align-top">
                                <div class="font-mono font-medium break-all">
                                    {{ identifier.display_value }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ identifier.type_label }}
                                </div>
                            </TableCell>
                            <TableCell class="align-top whitespace-normal">
                                <Badge
                                    v-if="identifier.current"
                                    variant="outline"
                                >
                                    Current
                                </Badge>
                                <template v-else>
                                    <Badge variant="secondary">Retired</Badge>
                                    <div
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            formatDateTime(
                                                identifier.retired_at,
                                            )
                                        }}
                                        by {{ identifier.retired_by }} —
                                        {{ identifier.retire_reason }}
                                    </div>
                                </template>
                            </TableCell>
                            <TableCell
                                class="align-top text-sm text-muted-foreground"
                            >
                                {{ formatDateTime(identifier.added_at) }}
                                <div class="text-xs">
                                    by {{ identifier.added_by }}
                                </div>
                            </TableCell>
                            <TableCell
                                v-if="can.manage"
                                class="text-right align-top"
                            >
                                <ReasonDialog
                                    v-if="identifier.current"
                                    :title="`Retire ${identifier.display_value}`"
                                    description="Use for a typing mistake or an ID no longer in use. It stays in the history and cannot be reused. A profile must keep at least one current ID, so add the correct ID first."
                                    confirm-label="Retire ID"
                                    trigger-label="Retire"
                                    destructive
                                    :action="
                                        ClientIdentifierController.destroy({
                                            clientProfile: profile.id,
                                            clientIdentifier: identifier.id,
                                        })
                                    "
                                    :data="{ version: profile.version }"
                                />
                            </TableCell>
                        </TableRow>
                        <TableEmpty
                            v-if="profile.identifiers.length === 0"
                            :colspan="can.manage ? 4 : 3"
                        >
                            No IDs recorded.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <Card id="client-history">
            <CardHeader>
                <CardTitle>History</CardTitle>
                <CardDescription>
                    Every change, with who made it and why. Newest first.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ol class="divide-y">
                    <li
                        v-for="revision in revisions.data"
                        :key="revision.revision_number"
                        class="flex flex-col gap-1 py-3"
                    >
                        <div
                            class="flex flex-wrap items-baseline justify-between gap-2"
                        >
                            <span class="font-medium">
                                {{ revision.action_label }}
                                <span class="font-normal text-muted-foreground">
                                    · version {{ revision.revision_number }}
                                </span>
                            </span>
                            <span class="text-sm text-muted-foreground">
                                {{ formatDateTime(revision.occurred_at) }} ·
                                {{ revision.actor }}
                            </span>
                        </div>
                        <p class="text-sm break-words">{{ revision.reason }}</p>
                        <ul
                            v-if="revision.changes"
                            class="space-y-0.5 text-sm text-muted-foreground"
                        >
                            <li
                                v-for="(change, field) in revision.changes"
                                :key="field"
                                class="break-words"
                            >
                                <span class="font-medium text-foreground">
                                    {{ fieldLabels[field] ?? field }}:
                                </span>
                                {{ formatValue(String(field), change.old) }}
                                →
                                {{ formatValue(String(field), change.new) }}
                            </li>
                        </ul>
                    </li>
                </ol>
                <Pagination :page="revisions" noun="changes" />
            </CardContent>
        </Card>
    </div>
</template>
