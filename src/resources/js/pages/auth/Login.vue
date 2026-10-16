<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
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
import PasskeyVerify from '@/components/PasskeyVerify.vue';

defineOptions({
    layout: {
        title: 'Welcome Back 👋',
        description: 'Enter your email and password to sign in',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
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
            <Label for="email" class="sr-only">Email address</Label>
            <Input
                id="email"
                type="email"
                name="email"
                required
                v-focus
                :tabindex="1"
                autocomplete="email"
                placeholder="Enter your email address"
            />
            <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="password" class="sr-only">Password</Label>
            <PasswordInput
                id="password"
                name="password"
                required
                :tabindex="2"
                autocomplete="current-password"
                placeholder="Enter your password"
            />
            <InputError :message="errors.password" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <Label
                for="remember"
                class="flex items-center gap-2 font-normal text-muted-foreground"
            >
                <Checkbox id="remember" name="remember" :tabindex="3" />
                <span>Remember me</span>
            </Label>
            <TextLink v-if="canResetPassword" :href="request()" :tabindex="5">
                Forgot password?
            </TextLink>
        </div>

        <Button
            type="submit"
            class="w-full"
            :tabindex="4"
            :disabled="processing"
            data-test="login-button"
        >
            <Spinner v-if="processing" />
            Sign in
        </Button>
    </Form>

    <div class="text-center text-sm text-muted-foreground">
        Don't have an account yet?
        <TextLink :href="register()" :tabindex="6">Sign up</TextLink>
    </div>
</template>
