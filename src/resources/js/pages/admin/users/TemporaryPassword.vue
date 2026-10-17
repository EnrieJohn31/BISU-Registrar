<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check, Copy, KeyRound, Printer } from '@lucide/vue';
import { ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDateTime } from '@/lib/format';
import { index, show } from '@/routes/admin/users';

const props = defineProps<{
    user: { id: number; name: string; email: string };
    temporaryPassword: string;
    expiresAt: string;
    context: 'provisioned' | 'reissued';
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: index() }],
    },
});

const copied = ref(false);

const copy = async () => {
    await navigator.clipboard.writeText(props.temporaryPassword);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const print = () => window.print();
</script>

<template>
    <Head title="Temporary password" />

    <div class="flex max-w-xl flex-col gap-6 p-4">
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <KeyRound class="size-5" />
                    {{
                        context === 'provisioned'
                            ? 'Staff account created'
                            : 'Temporary password issued'
                    }}
                </CardTitle>
                <CardDescription>
                    {{ user.name }} · {{ user.email }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div>
                    <p class="mb-2 text-sm text-muted-foreground">
                        Temporary password
                    </p>
                    <p
                        class="rounded-lg border bg-muted px-4 py-3 text-center font-mono text-2xl tracking-widest select-all"
                        data-test="temporary-password"
                    >
                        {{ temporaryPassword }}
                    </p>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Expires {{ formatDateTime(expiresAt) }}. It stops
                        working once the person sets their own password.
                    </p>
                </div>

                <Alert>
                    <AlertTitle>This password is shown only once</AlertTitle>
                    <AlertDescription>
                        Hand it to the person directly. Do not send it by chat
                        or email. At first sign-in they must set a new password,
                        and set up two-factor if their role requires it.
                    </AlertDescription>
                </Alert>
            </CardContent>
            <CardFooter class="flex flex-wrap gap-2 print:hidden">
                <Button variant="outline" @click="copy">
                    <Check v-if="copied" />
                    <Copy v-else />
                    {{ copied ? 'Copied' : 'Copy' }}
                </Button>
                <Button variant="outline" @click="print">
                    <Printer />
                    Print
                </Button>
                <Button as-child>
                    <Link :href="show(user.id)">Done</Link>
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>
