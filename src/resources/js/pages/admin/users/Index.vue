<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, ShieldCheck } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { reactive, watch } from 'vue';
import RoleBadges from '@/components/admin/RoleBadges.vue';
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
import { create, index, show } from '@/routes/admin/users';
import type { OfficeOption, Paginated, RoleOption, UserSummary } from '@/types';

type Filters = {
    search: string;
    role: string | null;
    office: string | null;
    status: string | null;
};

const props = defineProps<{
    users: Paginated<UserSummary>;
    filters: Filters;
    roleOptions: RoleOption[];
    officeOptions: OfficeOption[];
    canCreate: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: index() }],
    },
});

const ALL = 'all';

const filters = reactive({
    search: props.filters.search ?? '',
    role: props.filters.role ?? ALL,
    office: props.filters.office ?? ALL,
    status: props.filters.status ?? ALL,
});

const apply = () => {
    router.get(
        index().url,
        {
            search: filters.search || undefined,
            role: filters.role === ALL ? undefined : filters.role,
            office: filters.office === ALL ? undefined : filters.office,
            status: filters.status === ALL ? undefined : filters.status,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(() => filters.search, useDebounceFn(apply, 300));
watch(() => [filters.role, filters.office, filters.status], apply);
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="Users"
                description="Staff accounts, their roles, and client accounts"
            />
            <Button v-if="canCreate" as-child>
                <Link :href="create()">
                    <Plus />
                    Create staff account
                </Link>
            </Button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <Input
                v-model="filters.search"
                type="search"
                placeholder="Search name or email"
                aria-label="Search name or email"
            />
            <Select v-model="filters.role">
                <SelectTrigger class="w-full" aria-label="Role">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">All roles</SelectItem>
                    <SelectItem
                        v-for="role in roleOptions"
                        :key="role.value"
                        :value="role.value"
                    >
                        {{ role.label }}
                    </SelectItem>
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
            <Select v-model="filters.status">
                <SelectTrigger class="w-full" aria-label="Status">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="ALL">Any status</SelectItem>
                    <SelectItem value="active">Active</SelectItem>
                    <SelectItem value="deactivated">Deactivated</SelectItem>
                    <SelectItem value="staff">Staff only</SelectItem>
                    <SelectItem value="clients">Clients only</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Roles</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Two-factor</TableHead>
                        <TableHead>Last sign-in</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="user in users.data" :key="user.id">
                        <TableCell>
                            <Link
                                :href="show(user.id)"
                                class="font-medium hover:underline"
                            >
                                {{ user.name }}
                            </Link>
                            <div class="text-xs text-muted-foreground">
                                {{ user.email }}
                            </div>
                        </TableCell>
                        <TableCell class="whitespace-normal">
                            <RoleBadges :roles="user.roles" />
                        </TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    user.status === 'active'
                                        ? 'outline'
                                        : 'destructive'
                                "
                            >
                                {{
                                    user.status === 'active'
                                        ? 'Active'
                                        : 'Deactivated'
                                }}
                            </Badge>
                            <Badge
                                v-if="user.must_change_password"
                                variant="secondary"
                                class="ml-1"
                            >
                                First sign-in pending
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <span
                                v-if="user.two_factor_enabled"
                                class="inline-flex items-center gap-1 text-sm"
                            >
                                <ShieldCheck class="size-4" /> On
                            </span>
                            <span v-else class="text-sm text-muted-foreground">
                                Off
                            </span>
                        </TableCell>
                        <TableCell class="text-sm text-muted-foreground">
                            {{ formatDateTime(user.last_login_at) }}
                        </TableCell>
                    </TableRow>
                    <TableEmpty v-if="users.data.length === 0" :colspan="5">
                        No accounts match these filters.
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <Pagination :page="users" noun="accounts" />
    </div>
</template>
