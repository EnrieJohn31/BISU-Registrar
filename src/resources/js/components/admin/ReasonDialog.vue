<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref, useId } from 'vue';
import { toast } from 'vue-sonner';
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

// Every account- or record-changing action records a reason in the audit trail.
// `data` adds fixed fields to the request, for example the profile `version`
// that lets the server reject an edit made from a stale screen.
const props = withDefaults(
    defineProps<{
        title: string;
        description: string;
        action: UrlMethodPair;
        confirmLabel: string;
        triggerLabel?: string;
        destructive?: boolean;
        disabled?: boolean;
        data?: Record<string, string | number>;
        reasonPlaceholder?: string;
    }>(),
    {
        triggerLabel: undefined,
        destructive: false,
        disabled: false,
        data: () => ({}),
        reasonPlaceholder: 'Recorded in the audit trail',
    },
);

const open = ref(false);
const id = useId();
const form = useForm({ reason: '' });

// Any error that is not about the reason field, such as a rule the server enforces.
const otherError = computed(
    () =>
        Object.entries(form.errors).find(([field]) => field !== 'reason')?.[1],
);

const submit = () => {
    form.transform((payload) => ({ ...payload, ...props.data })).submit(
        props.action,
        {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false;
                form.reset();
            },
            onError: (errors) => {
                // Someone else changed the record: show the latest version.
                if (errors.version) {
                    toast.error(errors.version);
                    open.value = false;
                    router.reload();
                }
            },
        },
    );
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
                        :placeholder="reasonPlaceholder"
                        :aria-invalid="Boolean(form.errors.reason)"
                    />
                    <InputError :message="form.errors.reason ?? otherError" />
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
