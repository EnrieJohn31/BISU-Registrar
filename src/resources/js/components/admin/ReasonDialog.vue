<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { useForm } from '@inertiajs/vue3';
import { ref, useId } from 'vue';
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
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

// Every account-changing administrator action records a reason in the audit trail.
const props = withDefaults(
    defineProps<{
        title: string;
        description: string;
        action: UrlMethodPair;
        confirmLabel: string;
        triggerLabel?: string;
        destructive?: boolean;
        disabled?: boolean;
    }>(),
    { triggerLabel: undefined, destructive: false, disabled: false },
);

const open = ref(false);
const id = useId();
const form = useForm({ reason: '' });

const submit = () => {
    form.submit(props.action, {
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
            <Button
                :variant="destructive ? 'destructive' : 'outline'"
                size="sm"
                :disabled="disabled"
            >
                {{ triggerLabel ?? confirmLabel }}
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-lg">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label :for="id">Reason</Label>
                    <Textarea
                        :id="id"
                        v-model="form.reason"
                        rows="3"
                        required
                        placeholder="Recorded in the audit trail"
                        :aria-invalid="Boolean(form.errors.reason)"
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
                        :variant="destructive ? 'destructive' : 'default'"
                        :disabled="form.processing"
                    >
                        <Spinner v-if="form.processing" />
                        {{ confirmLabel }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
