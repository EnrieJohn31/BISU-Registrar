<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-dvh lg:grid-cols-2">
        <!-- Image panel: place an <img class="absolute inset-0 size-full object-cover"> here. -->
        <div class="relative hidden bg-blue-600 lg:block" />

        <div class="flex flex-col px-6 py-8 sm:px-12 lg:px-16">
            <Link
                :href="home()"
                class="inline-flex w-fit items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ChevronLeft class="size-4" />
                Back to the website
            </Link>

            <div
                class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center gap-8 py-8"
            >
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
