<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    IdCard,
    ScrollText,
    ShieldCheck,
    TriangleAlert,
    UserPlus,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import RoleBadges from '@/components/admin/RoleBadges.vue';
import PageHeader from '@/components/PageHeader.vue';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index as auditIndex } from '@/routes/admin/audit';
import {
    create as usersCreate,
    index as usersIndex,
} from '@/routes/admin/users';
import {
    create as clientsCreate,
    index as clientsIndex,
} from '@/routes/registrar/clients';
import { edit as securityEdit } from '@/routes/security';
import type { NavItem, VerificationStateValue } from '@/types';

type Access = {
    clients: boolean;
    createClient: boolean;
    users: boolean;
    createUser: boolean;
    audit: boolean;
};

type ClientCounts = {
    total: number;
    verified: number;
    unverified: number;
    needs_review: number;
    updated_this_week: number;
};

type Shortcut = NavItem & { description: string };

const props = defineProps<{
    access: Access;
    clientCounts: ClientCounts | null;
}>();

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

const auth = computed(() => page.props.auth);
const isStaff = computed(() => auth.value.roles.length > 0);

const today = new Intl.DateTimeFormat('en-PH', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric',
}).format(new Date());

const stateTiles = computed(() => {
    const counts = props.clientCounts;

    if (counts === null) {
        return [];
    }

    const tile = (
        label: string,
        count: number,
        state?: VerificationStateValue,
    ) => ({
        label,
        count,
        state,
        href: clientsIndex(state ? { query: { state } } : undefined),
    });

    return [
        tile('All client records', counts.total),
        tile('Verified', counts.verified, 'verified'),
        tile('Unverified', counts.unverified, 'unverified'),
        tile('Needs review', counts.needs_review, 'needs_review'),
    ];
});

const shortcuts = computed<Shortcut[]>(() => [
    ...(props.access.clients
        ? [
              {
                  title: 'Client records',
                  description:
                      'Look up the students and alumni who request services.',
                  href: clientsIndex(),
                  icon: IdCard,
              },
          ]
        : []),
    ...(props.access.createClient
        ? [
              {
                  title: 'New client',
                  description: 'Record a person with their first ID.',
                  href: clientsCreate(),
                  icon: UserPlus,
              },
          ]
        : []),
    ...(props.access.users
        ? [
              {
                  title: 'Users',
                  description: 'Staff accounts, roles and client accounts.',
                  href: usersIndex(),
                  icon: UsersRound,
              },
          ]
        : []),
    ...(props.access.createUser
        ? [
              {
                  title: 'Create staff account',
                  description: 'Issue a temporary password in person.',
                  href: usersCreate(),
                  icon: UserPlus,
              },
          ]
        : []),
    ...(props.access.audit
        ? [
              {
                  title: 'Audit trail',
                  description:
                      'Review recorded actions. Nothing can be edited.',
                  href: auditIndex(),
                  icon: ScrollText,
              },
          ]
        : []),
    {
        title: 'Security settings',
        description: 'Your password, two-factor sign-in and passkeys.',
        href: securityEdit(),
        icon: ShieldCheck,
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-8 p-4">
        <PageHeader
            :title="`Welcome, ${auth.user.name}`"
            :description="`${today} · BISU Registrar, Balilihan Campus`"
        >
            <RoleBadges v-if="isStaff" :roles="auth.roles" />
        </PageHeader>

        <Card v-if="!isStaff" class="border-dashed">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <IdCard class="size-5 text-primary" />
                    Link your Student or Alumni ID
                </CardTitle>
                <CardDescription>
                    Your account is not yet linked to a verified Student or
                    Alumni ID. Document requests open once linking is available
                    and your identity is confirmed by the Registrar's Office.
                </CardDescription>
            </CardHeader>
        </Card>

        <section
            v-if="clientCounts"
            aria-labelledby="client-overview"
            class="space-y-3"
        >
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="client-overview" class="text-lg font-semibold">
                    Client records at a glance
                </h2>
                <p class="text-sm text-muted-foreground">
                    {{ clientCounts.updated_this_week }} updated in the last 7
                    days
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Link
                    v-for="tile in stateTiles"
                    :key="tile.label"
                    :href="tile.href"
                    :class="
                        cn(
                            'rounded-xl border bg-card p-4 transition-colors outline-none hover:border-primary/40 hover:bg-accent focus-visible:ring-[3px] focus-visible:ring-ring/50',
                            tile.state === 'needs_review' &&
                                tile.count > 0 &&
                                'border-amber-600/50 bg-amber-50 dark:bg-amber-950/30',
                        )
                    "
                >
                    <span
                        class="flex items-center gap-1.5 text-sm text-muted-foreground"
                    >
                        <TriangleAlert
                            v-if="
                                tile.state === 'needs_review' && tile.count > 0
                            "
                            class="size-4 text-amber-700 dark:text-amber-400"
                            aria-hidden="true"
                        />
                        {{ tile.label }}
                    </span>
                    <span
                        class="mt-1 block text-3xl font-semibold tracking-tight tabular-nums"
                    >
                        {{ tile.count }}
                    </span>
                </Link>
            </div>
        </section>

        <section aria-labelledby="shortcuts" class="space-y-3">
            <h2 id="shortcuts" class="text-lg font-semibold">Shortcuts</h2>
            <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <li
                    v-for="shortcut in shortcuts"
                    :key="shortcut.title"
                    class="relative flex items-start gap-3 rounded-xl border bg-card p-4 transition-colors focus-within:ring-[3px] focus-within:ring-ring/50 hover:border-primary/40 hover:bg-accent"
                >
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        aria-hidden="true"
                    >
                        <component :is="shortcut.icon" class="size-5" />
                    </span>
                    <span class="min-w-0 space-y-0.5">
                        <!-- The whole card is clickable; the link's name stays the title. -->
                        <Link
                            :href="shortcut.href"
                            class="font-medium outline-none after:absolute after:inset-0 after:rounded-xl"
                        >
                            {{ shortcut.title }}
                        </Link>
                        <span class="block text-sm text-muted-foreground">
                            {{ shortcut.description }}
                        </span>
                    </span>
                </li>
            </ul>
            <p v-if="isStaff" class="text-sm text-muted-foreground">
                Office modules appear here as they are released.
            </p>
        </section>
    </div>
</template>
