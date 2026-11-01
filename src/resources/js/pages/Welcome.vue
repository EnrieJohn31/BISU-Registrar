<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Receipt, Ticket } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();

const services = [
    {
        icon: FileText,
        title: 'Document requests',
        description:
            "Request documents from the Registrar's Office and follow their progress.",
    },
    {
        icon: Ticket,
        title: 'Queue tickets',
        description: 'Get a queue number for the Registrar or Cashier counter.',
    },
    {
        icon: Receipt,
        title: 'Payments',
        description:
            'Fees for paid requests are settled at the Cashier and recorded against the request.',
    },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-dvh flex-col bg-background text-foreground">
        <div class="h-1.5 bg-gold" aria-hidden="true" />

        <header
            class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6"
        >
            <div class="flex min-w-0 items-center gap-3">
                <AppLogoIcon class="size-10 shrink-0" />
                <div class="min-w-0">
                    <p class="truncate leading-tight font-semibold">
                        BISU Registrar
                    </p>
                    <p class="truncate text-sm text-muted-foreground">
                        Balilihan Campus
                    </p>
                </div>
            </div>
            <nav class="flex shrink-0 items-center gap-2">
                <Button v-if="$page.props.auth.user" as-child>
                    <Link :href="dashboard()">Dashboard</Link>
                </Button>
                <template v-else>
                    <Button v-if="canRegister" variant="ghost" as-child>
                        <Link :href="register()">Register</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 pb-12 sm:px-6">
            <section
                class="relative overflow-hidden rounded-2xl bg-navy px-6 py-10 text-white sm:px-10 sm:py-14"
            >
                <div
                    class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,rgb(72_0_144/0.6),transparent_55%)]"
                    aria-hidden="true"
                />
                <div
                    class="relative flex flex-col-reverse items-start gap-8 md:flex-row md:items-center md:justify-between"
                >
                    <div class="max-w-xl space-y-4">
                        <p
                            class="text-xs font-semibold tracking-[0.2em] text-gold uppercase"
                        >
                            Bohol Island State University
                        </p>
                        <h1
                            class="text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Registrar and Cashier services
                        </h1>
                        <p class="text-white/80">
                            Document requests, queuing and transactions for the
                            Registrar and Cashier offices of Bohol Island State
                            University – Balilihan Campus.
                        </p>
                        <Button
                            as-child
                            size="lg"
                            class="bg-gold text-gold-foreground hover:bg-gold/90"
                        >
                            <Link
                                :href="
                                    $page.props.auth.user
                                        ? dashboard()
                                        : login()
                                "
                            >
                                {{
                                    $page.props.auth.user
                                        ? 'Go to your dashboard'
                                        : 'Log in to continue'
                                }}
                            </Link>
                        </Button>
                    </div>
                    <AppLogoIcon
                        large
                        class="size-28 shrink-0 drop-shadow-xl sm:size-40"
                    />
                </div>
            </section>

            <section aria-labelledby="services" class="mt-10 space-y-4">
                <div class="space-y-1">
                    <h2 id="services" class="text-xl font-semibold">
                        What this system is for
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        It is being built in stages. Each service opens once the
                        offices approve it.
                    </p>
                </div>
                <ul class="grid gap-4 md:grid-cols-3">
                    <li
                        v-for="service in services"
                        :key="service.title"
                        class="rounded-xl border bg-card p-5"
                    >
                        <span
                            class="mb-3 flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            aria-hidden="true"
                        >
                            <component :is="service.icon" class="size-5" />
                        </span>
                        <h3 class="font-medium">{{ service.title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ service.description }}
                        </p>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="border-t">
            <p
                class="mx-auto max-w-6xl px-4 py-6 text-sm text-muted-foreground sm:px-6"
            >
                Bohol Island State University – Balilihan Campus · Available on
                the BISU campus network.
            </p>
        </footer>
    </div>
</template>
