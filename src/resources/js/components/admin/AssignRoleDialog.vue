<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
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
import { store } from '@/routes/admin/users/roles';
import type { OfficeOption, RoleOption } from '@/types';

const props = defineProps<{
    userId: number;
    roleOptions: RoleOption[];
    officeOptions: OfficeOption[];
    disabled?: boolean;
}>();

const open = ref(false);
const form = useForm({ role: '', office_id: '', reason: '' });

const selectedRole = computed(() =>
    props.roleOptions.find((option) => option.value === form.role),
);

const availableOffices = computed(() => {
    const role = selectedRole.value;

    if (!role || role.campus_wide) {
        return [];
    }

    return props.officeOptions.filter((office) =>
        role.offices.includes(office.code),
    );
});

watch(
    () => form.role,
    () => {
        form.office_id =
            availableOffices.value.length === 1
                ? String(availableOffices.value[0].id)
                : '';
    },
);

const submit = () => {
    form.submit(store(props.userId), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button size="sm" :disabled="disabled">Assign role</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-lg">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Assign a role</DialogTitle>
                    <DialogDescription>
                        The change takes effect on the person's next request and
                        is recorded in the audit trail.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="assign-role">Role</Label>
                    <Select v-model="form.role">
                        <SelectTrigger id="assign-role" class="w-full">
                            <SelectValue placeholder="Choose a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in roleOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="selectedRole?.requires_two_factor"
                        class="text-xs text-muted-foreground"
                    >
                        This role requires two-factor authentication.
                    </p>
                    <InputError :message="form.errors.role" />
                </div>

                <div v-if="availableOffices.length" class="grid gap-2">
                    <Label for="assign-office">Office</Label>
                    <Select v-model="form.office_id">
                        <SelectTrigger id="assign-office" class="w-full">
                            <SelectValue placeholder="Choose an office" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="office in availableOffices"
                                :key="office.id"
                                :value="String(office.id)"
                            >
                                {{ office.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.office_id" />
                </div>
                <p
                    v-else-if="selectedRole?.campus_wide"
                    class="text-sm text-muted-foreground"
                >
                    Campus-wide role: no office needed.
                </p>

                <div class="grid gap-2">
                    <Label for="assign-reason">Reason</Label>
                    <Textarea
                        id="assign-reason"
                        v-model="form.reason"
                        rows="3"
                        required
                        placeholder="Recorded in the audit trail"
                    />
                    <InputError :message="form.errors.reason" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="form.processing || !form.role"
                    >
                        <Spinner v-if="form.processing" />
                        Assign role
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
