<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { IdCard, ScrollText, UsersRound } from '@lucide/vue';
import { computed } from 'vue';
import RoleBadges from '@/components/admin/RoleBadges.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { usePermissions } from '@/composables/usePermissions';
import { dashboard } from '@/routes';
import { index as auditIndex } from '@/routes/admin/audit';
import { index as usersIndex } from '@/routes/admin/users';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const { can } = usePermissions();

const auth = computed(() => page.props.auth);
const isStaff = computed(() => auth.value.roles.length > 0);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            :title="`Welcome, ${auth.user.name}`"
            description="BISU Registrar · Balilihan Campus"
        />

        <div class="grid gap-4 lg:grid-cols-2">
            <Card v-if="isStaff">
                <CardHeader>
                    <CardTitle>Your roles</CardTitle>
                    <CardDescription>
                        What you can do in BISU Registrar. Office modules appear
                        here as they are released.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <RoleBadges :roles="auth.roles" />
                </CardContent>
            </Card>

            <Card v-else>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <IdCard class="size-5" />
                        Link your Student or Alumni ID
                    </CardTitle>
                    <CardDescription>
                        Your account is not yet linked to a verified Student or
                        Alumni ID. Document requests open once linking is
                        available and your identity is confirmed by the
                        Registrar's Office.
                    </CardDescription>
                </CardHeader>
            </Card>

            <Card v-if="can('users.view') || can('audit.view')">
                <CardHeader>
                    <CardTitle>Administration</CardTitle>
                    <CardDescription>
                        Manage staff accounts and review recorded actions.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-wrap gap-2">
                    <Button v-if="can('users.view')" variant="outline" as-child>
                        <Link :href="usersIndex()">
                            <UsersRound />
                            Users
                        </Link>
                    </Button>
                    <Button v-if="can('audit.view')" variant="outline" as-child>
                        <Link :href="auditIndex()">
                            <ScrollText />
                            Audit trail
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
