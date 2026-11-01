<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-dvh lg:grid-cols-2">
        <aside
            class="relative hidden overflow-hidden bg-navy text-white lg:flex lg:flex-col lg:justify-between lg:p-12"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgb(72_0_144/0.55),transparent_60%)]"
                aria-hidden="true"
            />
            <div
                class="absolute inset-x-0 top-0 h-1.5 bg-gold"
                aria-hidden="true"
            />

            <p
                class="relative text-xs font-semibold tracking-[0.2em] text-white/70 uppercase"
            >
                Bohol Island State University
            </p>

            <div class="relative flex flex-col items-start gap-6">
                <AppLogoIcon large class="size-40 drop-shadow-xl" />
                <div class="space-y-2">
                    <p class="text-4xl font-bold tracking-tight">
                        BISU Registrar
                    </p>
                    <p class="text-lg text-gold">Balilihan Campus</p>
                </div>
                <p class="max-w-md text-white/80">
                    Document requests, queuing and transactions for the
                    Registrar and Cashier offices.
                </p>
            </div>

            <p class="relative text-sm text-white/60">
                Available on the BISU campus network.
            </p>
        </aside>

        <div class="flex flex-col px-6 py-8 sm:px-12 lg:px-16">
            <Link
                :href="home()"
                class="inline-flex w-fit items-center gap-1 rounded-sm text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ChevronLeft class="size-4" />
                Back to the website
            </Link>

            <div
                class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center gap-8 py-8"
            >
                <div class="flex items-center gap-3 lg:hidden">
                    <AppLogoIcon class="size-12" />
                    <div>
                        <p class="leading-tight font-semibold">
                            BISU Registrar
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Balilihan Campus
                        </p>
                    </div>
                </div>

                <div class="space-y-2" v-if="title || description">
                    <h1 class="text-3xl font-bold tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <div class="space-y-6">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
