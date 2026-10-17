<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { IdCard, LayoutGrid, ScrollText, UsersRound } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { dashboard } from '@/routes';
import { index as auditIndex } from '@/routes/admin/audit';
import { index as usersIndex } from '@/routes/admin/users';
import { index as clientsIndex } from '@/routes/registrar/clients';
import type { NavItem } from '@/types';

const { can } = usePermissions();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const registrarNavItems = computed<NavItem[]>(() =>
    can('clients.view')
        ? [{ title: 'Client records', href: clientsIndex(), icon: IdCard }]
        : [],
);

const adminNavItems = computed<NavItem[]>(() => [
    ...(can('users.view')
        ? [{ title: 'Users', href: usersIndex(), icon: UsersRound }]
        : []),
    ...(can('audit.view')
        ? [{ title: 'Audit trail', href: auditIndex(), icon: ScrollText }]
        : []),
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain
                v-if="registrarNavItems.length"
                :items="registrarNavItems"
                label="Registrar"
            />
            <NavMain
                v-if="adminNavItems.length"
                :items="adminNavItems"
                label="Administration"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
