<script setup lang="ts">
/**
 * The title block of a workspace page: the page's only h1, an optional
 * description, details such as badges (default slot) and page actions.
 */
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 space-y-2 break-words">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="max-w-3xl text-sm text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <div v-if="$slots.default" class="flex flex-wrap gap-2">
                <slot />
            </div>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
