<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Sign in',
        description:
            'Use the email and password for your BISU Registrar account.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div v-if="status" class="text-sm font-medium text-green-600">
        {{ status }}
    </div>

    <PasskeyVerify separator="Or" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-4"
    >
        <div class="grid gap-2">
            <Label for="email">Email address</Label>
            <Input
                id="email"
                type="email"
                name="email"
                required
                v-focus
                autocomplete="email"
                placeholder="name@example.com"
            />
            <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="password">Password</Label>
            <PasswordInput
                id="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <InputError :message="errors.password" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <Label
                for="remember"
                class="flex items-center gap-2 font-normal text-muted-foreground"
            >
                <Checkbox id="remember" name="remember" />
                <span>Remember me</span>
            </Label>
            <TextLink v-if="canResetPassword" :href="request()">
                Forgot password?
            </TextLink>
        </div>

        <Button
            type="submit"
            class="w-full"
            :disabled="processing"
            data-test="login-button"
        >
            <Spinner v-if="processing" />
            Sign in
        </Button>
    </Form>

    <div v-if="canRegister" class="text-center text-sm text-muted-foreground">
        Don't have an account yet?
        <TextLink :href="register()">Sign up</TextLink>
    </div>
</template>
