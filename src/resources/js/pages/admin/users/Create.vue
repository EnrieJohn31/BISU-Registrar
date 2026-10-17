<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { create, index, store } from '@/routes/admin/users';
import type { OfficeOption, RoleOption } from '@/types';

const props = defineProps<{
    roleOptions: RoleOption[];
    officeOptions: OfficeOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: index() },
            { title: 'Create staff account', href: create() },
        ],
    },
});

const form = useForm({
    name: '',
    email: '',
    office_id: '',
    roles: [] as string[],
    reason: '',
});

const officeCode = computed(
    () =>
        props.officeOptions.find(
            (office) => String(office.id) === form.office_id,
        )?.code ?? null,
);

const isAvailable = (role: RoleOption): boolean =>
    role.campus_wide ||
    (officeCode.value !== null && role.offices.includes(officeCode.value));

const toggleRole = (value: string, checked: boolean) => {
    form.roles = checked
        ? [...form.roles, value]
        : form.roles.filter((role) => role !== value);
};

// Drop roles that are not valid in the newly chosen office.
watch(officeCode, () => {
    form.roles = form.roles.filter((value) => {
        const role = props.roleOptions.find((option) => option.value === value);

        return role ? isAvailable(role) : false;
    });
});

const submit = () => form.submit(store());
</script>

<template>
    <Head title="Create staff account" />

    <div class="flex max-w-2xl flex-col gap-6 p-4">
        <Heading
            title="Create staff account"
            description="The person receives a temporary password in person and must replace it at first sign-in."
        />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">Full name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    required
                    autocomplete="off"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="off"
                />
                <p class="text-xs text-muted-foreground">
                    Used to sign in. Marked verified because you are creating
                    the account for this person directly.
                </p>
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="office">Office</Label>
                <Select v-model="form.office_id">
                    <SelectTrigger id="office" class="w-full">
                        <SelectValue placeholder="Choose an office" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="office in officeOptions"
                            :key="office.id"
                            :value="String(office.id)"
                        >
                            {{ office.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p class="text-xs text-muted-foreground">
                    Not needed for a system administrator only.
                </p>
                <InputError :message="form.errors.office_id" />
            </div>

            <fieldset class="grid gap-3">
                <legend class="mb-2 text-sm font-medium">Roles</legend>
                <div
                    v-for="role in roleOptions"
                    :key="role.value"
                    class="flex items-start gap-3"
                >
                    <Checkbox
                        :id="`role-${role.value}`"
                        :model-value="form.roles.includes(role.value)"
                        :disabled="!isAvailable(role)"
                        @update:model-value="
                            (checked) =>
                                toggleRole(role.value, checked === true)
                        "
                    />
                    <div class="grid gap-0.5 leading-none">
                        <Label
                            :for="`role-${role.value}`"
                            :class="{ 'opacity-50': !isAvailable(role) }"
                        >
                            {{ role.label }}
                        </Label>
                        <p class="text-xs text-muted-foreground">
                            {{
                                role.campus_wide
                                    ? 'Campus-wide'
                                    : `Offices: ${role.offices.join(', ')}`
                            }}
                            <template v-if="role.requires_two_factor">
                                · requires two-factor
                            </template>
                        </p>
                    </div>
                </div>
                <InputError :message="form.errors.roles" />
            </fieldset>

            <div class="grid gap-2">
                <Label for="reason">Reason</Label>
                <Textarea
                    id="reason"
                    v-model="form.reason"
                    rows="3"
                    required
                    placeholder="For example: new Registrar processor, appointment memo no. …"
                />
                <InputError :message="form.errors.reason" />
            </div>

            <div class="flex items-center gap-3">
                <Button
                    type="submit"
                    :disabled="form.processing || form.roles.length === 0"
                >
                    <Spinner v-if="form.processing" />
                    Create account
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
