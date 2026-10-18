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
import { update } from '@/routes/registrar/clients';
import type { LabelledOption } from '@/types';

const props = defineProps<{
    profile: {
        id: number;
        display_name: string;
        category: string;
        version: number;
        verification_state: string;
    };
    categories: LabelledOption[];
}>();

const open = ref(false);
const form = useForm({
    display_name: props.profile.display_name,
    category: props.profile.category,
    reason: '',
});

// The version is sent with the request but is not a form field.
const versionError = computed(
    () => (form.errors as Record<string, string | undefined>).version,
);

// After a refresh (for example a stale edit) show the latest saved values.
watch(
    () => props.profile.version,
    () => {
        form.defaults({
            display_name: props.profile.display_name,
            category: props.profile.category,
            reason: '',
        });
        form.reset();
    },
);

const submit = () => {
    form.transform((payload) => ({
        ...payload,
        version: props.profile.version,
    })).submit(update(props.profile.id), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">Correct details</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-lg">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Correct client details</DialogTitle>
                    <DialogDescription>
                        The old and new values are kept in the profile history.
                        <template
                            v-if="profile.verification_state === 'verified'"
                        >
                            This profile is verified: after a correction it
                            returns to “Needs review” until it is verified
                            again.
                        </template>
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="correct-name">Name</Label>
                    <Input
                        id="correct-name"
                        v-model="form.display_name"
                        required
                        maxlength="255"
                    />
                    <InputError :message="form.errors.display_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="correct-category">Category</Label>
                    <Select v-model="form.category">
                        <SelectTrigger id="correct-category" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in categories"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        Descriptive only. It does not decide eligibility or
                        confirm enrolment or graduation.
                    </p>
                    <InputError :message="form.errors.category" />
                </div>

                <div class="grid gap-2">
                    <Label for="correct-reason">Reason</Label>
                    <Textarea
                        id="correct-reason"
                        v-model="form.reason"
                        rows="2"
                        required
                        placeholder="For example: name spelled differently on the source list"
                    />
                    <InputError :message="form.errors.reason ?? versionError" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary">
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" />
                        Save correction
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
