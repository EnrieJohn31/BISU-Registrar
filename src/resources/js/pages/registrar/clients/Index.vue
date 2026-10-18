<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { reactive, watch } from 'vue';
import VerificationBadge from '@/components/clients/VerificationBadge.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
import { create, index, show } from '@/routes/registrar/clients';
import type { ClientOptions, ClientSummary, Paginated } from '@/types';

type Filters = {
    search: string;
    category: string | null;
    state: string | null;
};

const props = defineProps<{
    profiles: Paginated<ClientSummary>;
    filters: Filters;
    options: ClientOptions;
    canCreate: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Client records', href: index() }],
    },
});

const ALL = 'all';

const filters = reactive({
    search: props.filters.search ?? '',
    category: props.filters.category ?? ALL,
    state: props.filters.state ?? ALL,
});

const apply = () => {
    router.get(
        index().url,
        {
            search: filters.search || undefined,
            category: filters.category === ALL ? undefined : filters.category,
            state: filters.state === ALL ? undefined : filters.state,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(() => filters.search, useDebounceFn(apply, 300));
watch(() => [filters.category, filters.state], apply);
</script>

<template>
    <Head title="Client records" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Client records"
                description="Students and alumni who request services. These records identify a person; they do not confirm enrolment, graduation or eligibility."
            />
            <Button v-if="canCreate" as-child>
                <Link :href="create()">
                    <Plus />
                    New client
                </Link>
            </Button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr]">
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="filters.search"
                    type="search"
                    class="pl-9"
                    placeholder="Search a name or the start of an ID"
                    aria-label="Search a name or the start of an ID"
                />
            </div>
            <Select v-model="filters.category">
                <SelectTrigger class="w-full" aria-label="Category">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">Any category</SelectItem>
                    <SelectItem
                        v-for="category in options.categories"
                        :key="category.value"
                        :value="category.value"
                    >
                        {{ category.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <Select v-model="filters.state">
                <SelectTrigger class="w-full" aria-label="Verification">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">Any verification</SelectItem>
                    <SelectItem
                        v-for="state in options.states"
                        :key="state.value"
                        :value="state.value"
                    >
                        {{ state.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
        <p class="-mt-3 text-xs text-muted-foreground">
            Search needs at least 2 characters. IDs are masked here; open a
            record to see the full ID.
        </p>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>IDs</TableHead>
                        <TableHead>Category</TableHead>
                        <TableHead>Verification</TableHead>
                        <TableHead>Updated</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="profile in profiles.data"
                        :key="profile.id"
                    >
                        <TableCell class="max-w-sm whitespace-normal">
                            <Link
                                :href="show(profile.id)"
                                class="font-medium break-words hover:underline"
                            >
                                {{ profile.display_name }}
                            </Link>
                        </TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <Badge
                                    v-for="identifier in profile.identifiers"
                                    :key="identifier.id"
                                    variant="secondary"
                                    class="font-mono"
                                >
                                    {{ identifier.type_label }}
                                    {{ identifier.masked }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell class="text-sm">
                            {{ profile.category_label }}
                        </TableCell>
                        <TableCell>
                            <VerificationBadge
                                :state="profile.verification_state"
                                :label="profile.verification_label"
                            />
                        </TableCell>
                        <TableCell class="text-sm text-muted-foreground">
                            {{ formatDateTime(profile.updated_at) }}
                        </TableCell>
                    </TableRow>
                    <TableEmpty v-if="profiles.data.length === 0" :colspan="5">
                        No client records match these filters.
                        <template v-if="canCreate">
                            Check the spelling, or create a new client.
                        </template>
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <Pagination :page="profiles" noun="client records" />
    </div>
</template>
