<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { reactive, ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
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
import { index } from '@/routes/admin/audit';
import type { AuditEventDetail, Paginated } from '@/types';

type Filters = {
    action: string | null;
    outcome: string | null;
    office: string | null;
    actor: string;
    from: string | null;
    to: string | null;
};

const props = defineProps<{
    events: Paginated<AuditEventDetail>;
    filters: Filters;
    actionOptions: { value: string; label: string }[];
    officeOptions: { code: string; name: string }[];
    scope: 'all' | 'office';
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Audit trail', href: index() }],
    },
});

const ALL = 'all';

const filters = reactive({
    action: props.filters.action ?? ALL,
    outcome: props.filters.outcome ?? ALL,
    office: props.filters.office ?? ALL,
    actor: props.filters.actor ?? '',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
});

const apply = () => {
    router.get(
        index().url,
        {
            action: filters.action === ALL ? undefined : filters.action,
            outcome: filters.outcome === ALL ? undefined : filters.outcome,
            office: filters.office === ALL ? undefined : filters.office,
            actor: filters.actor || undefined,
            from: filters.from || undefined,
            to: filters.to || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(() => filters.actor, useDebounceFn(apply, 300));
watch(
    () => [
        filters.action,
        filters.outcome,
        filters.office,
        filters.from,
        filters.to,
    ],
    apply,
);

const selected = ref<AuditEventDetail | null>(null);

const display = (value: unknown): string =>
    value === null || value === undefined || value === ''
        ? '—'
        : typeof value === 'object'
          ? JSON.stringify(value)
          : String(value);
</script>

<template>
    <Head title="Audit trail" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            title="Audit trail"
            :description="
                scope === 'all'
                    ? 'Recorded actions across all offices. Records cannot be edited or deleted.'
                    : 'Recorded actions in your office. Records cannot be edited or deleted.'
            "
        />

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <Select v-model="filters.action">
                <SelectTrigger class="w-full" aria-label="Action">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">All actions</SelectItem>
                    <SelectItem
                        v-for="option in actionOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select v-model="filters.outcome">
                <SelectTrigger class="w-full" aria-label="Outcome">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">Any outcome</SelectItem>
                    <SelectItem value="succeeded">Succeeded</SelectItem>
                    <SelectItem value="denied">Denied</SelectItem>
                </SelectContent>
            </Select>
            <Select v-model="filters.office">
                <SelectTrigger class="w-full" aria-label="Office">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">All offices</SelectItem>
                    <SelectItem
                        v-for="office in officeOptions"
                        :key="office.code"
                        :value="office.code"
                    >
                        {{ office.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Input
                v-model="filters.actor"
                type="search"
                placeholder="Actor name or email"
                aria-label="Actor"
            />
            <div class="grid gap-1">
                <Label for="from" class="sr-only">From</Label>
                <Input id="from" v-model="filters.from" type="date" />
            </div>
            <div class="grid gap-1">
                <Label for="to" class="sr-only">To</Label>
                <Input id="to" v-model="filters.to" type="date" />
            </div>
        </div>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>When</TableHead>
                        <TableHead>Action</TableHead>
                        <TableHead>Actor</TableHead>
                        <TableHead>Subject</TableHead>
                        <TableHead>Office</TableHead>
                        <TableHead>Reason</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="event in events.data"
                        :key="event.id"
                        class="cursor-pointer"
                        tabindex="0"
                        @click="selected = event"
                        @keydown.enter.prevent="selected = event"
                        @keydown.space.prevent="selected = event"
                    >
                        <TableCell class="text-sm text-muted-foreground">
                            {{ formatDateTime(event.occurred_at) }}
                        </TableCell>
                        <TableCell>
                            <span class="font-medium">
                                {{ event.action_label }}
                            </span>
                            <Badge
                                v-if="event.outcome === 'denied'"
                                variant="destructive"
                                class="ml-1"
                            >
                                Denied
                            </Badge>
                        </TableCell>
                        <TableCell>
                            {{ event.actor?.name ?? 'System' }}
                        </TableCell>
                        <TableCell class="text-sm">
                            {{
                                event.subject_label ??
                                (event.subject_type
                                    ? `${event.subject_type} #${event.subject_id}`
                                    : '—')
                            }}
                        </TableCell>
                        <TableCell class="text-sm">
                            {{ event.office ?? '—' }}
                        </TableCell>
                        <TableCell class="max-w-xs truncate text-sm">
                            {{ event.reason ?? '—' }}
                        </TableCell>
                    </TableRow>
                    <TableEmpty v-if="events.data.length === 0" :colspan="6">
                        No audit events match these filters.
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <Pagination :page="events" noun="events" />
    </div>

    <Sheet
        :open="selected !== null"
        @update:open="(open) => !open && (selected = null)"
    >
        <SheetContent class="w-full overflow-y-auto sm:max-w-lg">
            <template v-if="selected">
                <SheetHeader>
                    <SheetTitle>{{ selected.action_label }}</SheetTitle>
                    <SheetDescription>
                        {{ formatDateTime(selected.occurred_at) }} ·
                        {{
                            selected.outcome === 'denied'
                                ? 'Denied'
                                : 'Succeeded'
                        }}
                    </SheetDescription>
                </SheetHeader>

                <dl class="grid gap-4 px-4 pb-6 text-sm break-words">
                    <div>
                        <dt class="text-muted-foreground">Actor</dt>
                        <dd>
                            {{
                                selected.actor
                                    ? `${selected.actor.name} (${selected.actor.email})`
                                    : 'System'
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Subject</dt>
                        <dd>
                            {{ selected.subject_label ?? '—' }}
                            <span
                                v-if="selected.subject_type"
                                class="text-muted-foreground"
                            >
                                ({{ selected.subject_type }} #{{
                                    selected.subject_id
                                }})
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Reason</dt>
                        <dd class="whitespace-pre-line">
                            {{ selected.reason ?? '—' }}
                        </dd>
                    </div>
                    <div v-if="selected.changes">
                        <dt class="mb-1 text-muted-foreground">Changes</dt>
                        <dd>
                            <ul class="space-y-1">
                                <li
                                    v-for="(change, field) in selected.changes"
                                    :key="field"
                                >
                                    <span class="font-medium"
                                        >{{ field }}:</span
                                    >
                                    {{ display(change.old) }} →
                                    {{ display(change.new) }}
                                </li>
                            </ul>
                        </dd>
                    </div>
                    <div v-if="selected.context">
                        <dt class="mb-1 text-muted-foreground">Details</dt>
                        <dd>
                            <ul class="space-y-1">
                                <li
                                    v-for="(value, key) in selected.context"
                                    :key="key"
                                >
                                    <span class="font-medium">{{ key }}:</span>
                                    {{ display(value) }}
                                </li>
                            </ul>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Office</dt>
                        <dd>{{ selected.office ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">IP address</dt>
                        <dd>{{ selected.ip_address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Correlation id</dt>
                        <dd class="font-mono text-xs break-all">
                            {{ selected.correlation_id }}
                        </dd>
                    </div>
                </dl>
            </template>
        </SheetContent>
    </Sheet>
</template>
