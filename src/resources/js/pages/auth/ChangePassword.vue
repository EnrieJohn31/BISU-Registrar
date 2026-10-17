<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PasswordChangeController from '@/actions/App/Http/Controllers/Auth/PasswordChangeController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/format';
import { logout } from '@/routes';

defineProps<{
    passwordRules: string;
    expiresAt: string | null;
}>();

defineOptions({
    layout: {
        title: 'Set your password',
        description:
            'You signed in with a temporary password. Choose a new password that only you know.',
    },
});
</script>

<template>
    <Head title="Set your password" />

    <p v-if="expiresAt" class="text-sm text-muted-foreground">
        Your temporary password expires {{ formatDateTime(expiresAt) }}.
    </p>

    <Form
        v-bind="PasswordChangeController.update.form()"
        :reset-on-error="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-4"
    >
        <div class="grid gap-2">
            <Label for="password">New password</Label>
            <PasswordInput
                id="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                :passwordrules="passwordRules"
            />
            <InputError :message="errors.password" />
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation">Confirm new password</Label>
            <PasswordInput
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                :passwordrules="passwordRules"
            />
            <InputError :message="errors.password_confirmation" />
        </div>

        <Button
            type="submit"
            class="w-full"
            :disabled="processing"
            data-test="change-password-button"
        >
            <Spinner v-if="processing" />
            Save password
        </Button>
    </Form>

    <div class="text-center text-sm">
        <TextLink :href="logout()" as="button">Log out</TextLink>
    </div>
</template>
