<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ShieldAlert, ShieldCheck } from '@lucide/vue';
import UserCredentialController from '@/actions/App/Http/Controllers/Admin/UserCredentialController';
import UserRoleController from '@/actions/App/Http/Controllers/Admin/UserRoleController';
import UserStatusController from '@/actions/App/Http/Controllers/Admin/UserStatusController';
import AssignRoleDialog from '@/components/admin/AssignRoleDialog.vue';
import EditUserDialog from '@/components/admin/EditUserDialog.vue';
import ReasonDialog from '@/components/admin/ReasonDialog.vue';
import Heading from '@/components/Heading.vue';
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
import { index, show } from '@/routes/admin/users';
import type {
    AuditEventSummary,
    OfficeOption,
    RoleHistoryEntry,
    RoleOption,
    UserSummary,
} from '@/types';

type UserDetail = UserSummary & {
    created_at: string | null;
    deactivated_at: string | null;
    deactivation_reason: string | null;
    temporary_password_expires_at: string | null;
    requires_two_factor: boolean;
};

const props = defineProps<{
    user: UserDetail;
    history: RoleHistoryEntry[];
    events: AuditEventSummary[];
    roleOptions: RoleOption[];
    officeOptions: OfficeOption[];
    can: {
        update: boolean;
        manageRoles: boolean;
        changeStatus: boolean;
        manageCredentials: boolean;
    };
}>();

defineOptions({
    layout: (props: { user: UserDetail }) => ({
        breadcrumbs: [
            { title: 'Users', href: index() },
            { title: props.user.name, href: show(props.user.id) },
        ],
    }),
});

const isOwnAccount =
    !props.can.update &&
    !props.can.manageRoles &&
    !props.can.changeStatus &&
    !props.can.manageCredentials;
</script>

<template>
    <Head :title="user.name" />

    <div class="flex max-w-5xl flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <Heading :title="user.name" :description="user.email" />
                <div class="-mt-6 flex flex-wrap gap-2">
                    <Badge
                        :variant="
                            user.status === 'active' ? 'outline' : 'destructive'
                        "
                    >
                        {{
                            user.status === 'active' ? 'Active' : 'Deactivated'
                        }}
                    </Badge>
                    <Badge variant="secondary">
                        {{ user.roles.length ? 'Staff' : 'Client account' }}
                    </Badge>
                    <Badge v-if="user.two_factor_enabled" variant="outline">
                        <ShieldCheck /> Two-factor on
                    </Badge>
                    <Badge
                        v-else-if="user.requires_two_factor"
                        variant="destructive"
                    >
                        <ShieldAlert /> Two-factor required, not set up
                    </Badge>
                    <Badge v-if="user.must_change_password" variant="secondary">
                        First sign-in pending · temporary password expires
                        {{ formatDateTime(user.temporary_password_expires_at) }}
                    </Badge>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <EditUserDialog :user="user" :disabled="!can.update" />
                <ReasonDialog
                    title="Issue a temporary password"
                    description="Use after verifying the person in person. Their current password stops working, they are signed out everywhere, and they must set a new password at next sign-in."
                    confirm-label="Issue temporary password"
                    :action="
                        UserCredentialController.temporaryPassword(user.id)
                    "
                    :disabled="
                        !can.manageCredentials || user.status !== 'active'
                    "
                />
                <ReasonDialog
                    title="Reset two-factor authentication"
                    description="Use when the person lost their authenticator, after verifying them in person. Roles that require two-factor will ask them to set it up again."
                    confirm-label="Reset two-factor"
                    :action="UserCredentialController.resetTwoFactor(user.id)"
                    :disabled="
                        !can.manageCredentials || !user.two_factor_enabled
                    "
                />
                <ReasonDialog
                    v-if="user.status === 'active'"
                    title="Deactivate this account"
                    description="The person is signed out everywhere and can no longer sign in. Their roles, history and audit trail are kept."
                    confirm-label="Deactivate"
                    destructive
                    :action="UserStatusController.deactivate(user.id)"
                    :disabled="!can.changeStatus"
                />
                <ReasonDialog
                    v-else
                    title="Reactivate this account"
                    description="The person can sign in again with their existing roles."
                    confirm-label="Reactivate"
                    :action="UserStatusController.reactivate(user.id)"
                    :disabled="!can.changeStatus"
                />
            </div>
        </div>

        <Alert v-if="isOwnAccount">
            <AlertTitle>This is your own account</AlertTitle>
            <AlertDescription>
                Nobody can change their own roles or status here. Use your
                profile and security settings for your own details.
            </AlertDescription>
        </Alert>

        <Alert v-if="user.status === 'deactivated'" variant="destructive">
            <AlertTitle>
                Deactivated {{ formatDateTime(user.deactivated_at) }}
            </AlertTitle>
            <AlertDescription>{{ user.deactivation_reason }}</AlertDescription>
        </Alert>

        <Card>
            <CardHeader class="flex flex-row items-start justify-between gap-4">
                <div class="space-y-1.5">
                    <CardTitle>Active roles</CardTitle>
                    <CardDescription>
                        What this person may do. Changes apply on their next
                        request.
                    </CardDescription>
                </div>
                <AssignRoleDialog
                    :user-id="user.id"
                    :role-options="roleOptions"
                    :office-options="officeOptions"
                    :disabled="!can.manageRoles || user.status !== 'active'"
                />
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Role</TableHead>
                            <TableHead>Office</TableHead>
                            <TableHead class="text-right">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="role in user.roles" :key="role.id">
                            <TableCell class="font-medium">
                                {{ role.label }}
                            </TableCell>
                            <TableCell>
                                {{ role.office_name ?? 'Campus-wide' }}
                            </TableCell>
                            <TableCell class="text-right">
                                <ReasonDialog
                                    :title="`Revoke ${role.label}`"
                                    description="The person loses this role on their next request. The assignment stays in the role history."
                                    confirm-label="Revoke role"
                                    trigger-label="Revoke"
                                    destructive
                                    :action="
                                        UserRoleController.destroy({
                                            user: user.id,
                                            roleAssignment: role.id,
                                        })
                                    "
                                    :disabled="!can.manageRoles"
                                />
                            </TableCell>
                        </TableRow>
                        <TableEmpty v-if="user.roles.length === 0" :colspan="3">
                            No staff roles. This account can only act as a
                            client once linked to a Student/Alumni ID.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Role history</CardTitle>
                <CardDescription>
                    Every assignment, including revoked ones.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Role</TableHead>
                            <TableHead>Assigned</TableHead>
                            <TableHead>Revoked</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="entry in history" :key="entry.id">
                            <TableCell class="align-top">
                                <div class="font-medium">{{ entry.label }}</div>
                                <div class="text-xs text-muted-foreground">
                                    {{ entry.office_name ?? 'Campus-wide' }}
                                </div>
                            </TableCell>
                            <TableCell class="align-top whitespace-normal">
                                <div>
                                    {{ formatDateTime(entry.assigned_at) }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    by {{ entry.assigned_by }} —
                                    {{ entry.assignment_reason }}
                                </div>
                            </TableCell>
                            <TableCell class="align-top whitespace-normal">
                                <template v-if="entry.revoked_at">
                                    <div>
                                        {{ formatDateTime(entry.revoked_at) }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        by {{ entry.revoked_by }} —
                                        {{ entry.revoke_reason }}
                                    </div>
                                </template>
                                <Badge v-else variant="outline">Active</Badge>
                            </TableCell>
                        </TableRow>
                        <TableEmpty v-if="history.length === 0" :colspan="3">
                            No role assignments yet.
                        </TableEmpty>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Recent account activity</CardTitle>
                <CardDescription>
                    The latest audit events about this account.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ul class="divide-y">
                    <li
                        v-for="event in events"
                        :key="event.id"
                        class="flex flex-col gap-1 py-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <div class="font-medium">
                                {{ event.action_label }}
                                <Badge
                                    v-if="event.outcome === 'denied'"
                                    variant="destructive"
                                    class="ml-1"
                                >
                                    Denied
                                </Badge>
                            </div>
                            <div class="text-sm text-muted-foreground">
                                {{ event.actor ?? 'System' }}
                                <template v-if="event.reason">
                                    — {{ event.reason }}
                                </template>
                            </div>
                        </div>
                        <div class="text-sm text-muted-foreground">
                            {{ formatDateTime(event.occurred_at) }}
                        </div>
                    </li>
                    <li
                        v-if="events.length === 0"
                        class="py-3 text-sm text-muted-foreground"
                    >
                        No recorded activity yet.
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
