<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
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
import { create, index, store } from '@/routes/registrar/clients';
import type { ClientOptions } from '@/types';

defineProps<{
    options: ClientOptions;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Client records', href: index() },
            { title: 'New client', href: create() },
        ],
    },
});

const form = useForm({
    display_name: '',
    category: 'unknown',
    source_reference: '',
    identifier_type: 'student',
    identifier_value: '',
    reason: '',
});

const submit = () => form.submit(store());
</script>

<template>
    <Head title="New client" />

    <div class="flex max-w-2xl flex-col gap-6 p-4">
        <Heading
            title="New client"
            description="Record a person who requests services, with their first ID. Search first to avoid creating a duplicate."
        />

        <Alert>
            <AlertTitle>Use the minimum information</AlertTitle>
            <AlertDescription>
                Only a name, a category and an ID are stored. Do not record
                email, phone, birth date, address, grades or ID photos.
            </AlertDescription>
        </Alert>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="display_name">Name</Label>
                <Input
                    id="display_name"
                    v-model="form.display_name"
                    required
                    maxlength="255"
                    autocomplete="off"
                />
                <p class="text-xs text-muted-foreground">
                    Write it as it appears on the source. Two different people
                    may share a name; they are kept as separate records.
                </p>
                <InputError :message="form.errors.display_name" />
            </div>

            <div class="grid gap-2">
                <Label for="category">Category</Label>
                <Select v-model="form.category">
                    <SelectTrigger id="category" class="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="category in options.categories"
                            :key="category.value"
                            :value="category.value"
                        >
                            {{ category.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p class="text-xs text-muted-foreground">
                    Descriptive only. It does not decide eligibility or confirm
                    enrolment or graduation.
                </p>
                <InputError :message="form.errors.category" />
            </div>

            <div class="grid items-start gap-4 sm:grid-cols-[1fr_2fr]">
                <div class="grid gap-2">
                    <Label for="identifier_type">ID type</Label>
                    <Select v-model="form.identifier_type">
                        <SelectTrigger id="identifier_type" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="type in options.identifierTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.identifier_type" />
                </div>
                <div class="grid gap-2">
                    <Label for="identifier_value">ID</Label>
                    <Input
                        id="identifier_value"
                        v-model="form.identifier_value"
                        required
                        maxlength="64"
                        autocomplete="off"
                        spellcheck="false"
                    />
                    <InputError :message="form.errors.identifier_value" />
                </div>
            </div>
            <p class="-mt-4 text-xs text-muted-foreground">
                {{ options.identifierRule }} Leading zeroes are kept.
            </p>

            <div class="grid gap-2">
                <Label for="source_reference">Source</Label>
                <Input
                    id="source_reference"
                    v-model="form.source_reference"
                    required
                    maxlength="255"
                    placeholder="Where the details came from, for example: Registrar counter, walk-in"
                />
                <InputError :message="form.errors.source_reference" />
            </div>

            <div class="grid gap-2">
                <Label for="reason">Reason</Label>
                <Textarea
                    id="reason"
                    v-model="form.reason"
                    rows="2"
                    required
                    placeholder="Why this record is being created"
                />
                <InputError :message="form.errors.reason" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Create client
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="index()">Cancel</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
