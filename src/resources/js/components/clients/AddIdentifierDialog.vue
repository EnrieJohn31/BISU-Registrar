<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
import { store } from '@/routes/registrar/clients/identifiers';
import type { LabelledOption } from '@/types';

const props = defineProps<{
    profileId: number;
    version: number;
    identifierTypes: LabelledOption[];
    identifierRule: string;
}>();

const open = ref(false);
const form = useForm({
    identifier_type: 'alumni',
    identifier_value: '',
    reason: '',
});

// The version is sent with the request but is not a form field.
const versionError = computed(
    () => (form.errors as Record<string, string | undefined>).version,
);

const submit = () => {
    form.transform((payload) => ({
        ...payload,
        version: props.version,
    })).submit(store(props.profileId), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
        onError: (errors) => {
            if (errors.version) {
                toast.error(errors.version);
                open.value = false;
                router.reload();
            }
        },
    });
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">Add ID</Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-lg">
            <form class="space-y-4" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Add an ID to this person</DialogTitle>
                    <DialogDescription>
                        For example, an Alumni ID for someone who already has a
                        Student ID. This never creates a second person. An ID
                        already recorded anywhere, current or retired, is
                        refused.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="add-id-type">ID type</Label>
                    <Select v-model="form.identifier_type">
                        <SelectTrigger id="add-id-type" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in identifierTypes"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.identifier_type" />
                </div>

                <div class="grid gap-2">
                    <Label for="add-id-value">ID</Label>
                    <Input
                        id="add-id-value"
                        v-model="form.identifier_value"
                        required
                        maxlength="64"
                        autocomplete="off"
                        spellcheck="false"
                    />
                    <p class="text-xs text-muted-foreground">
                        {{ identifierRule }}
                    </p>
                    <InputError :message="form.errors.identifier_value" />
                </div>

                <div class="grid gap-2">
                    <Label for="add-id-reason">Reason</Label>
                    <Textarea
                        id="add-id-reason"
                        v-model="form.reason"
                        rows="2"
                        required
                        placeholder="For example: graduated, Alumni ID issued"
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
                        Add ID
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
