<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center gap-2 px-4 py-8 text-center">
        <component
            :is="icon"
            class="size-8 text-muted-foreground/70"
            aria-hidden="true"
        />
        <p class="font-medium text-foreground">{{ title }}</p>
        <p v-if="$slots.default" class="max-w-sm text-sm text-muted-foreground">
            <slot />
        </p>
    </div>
</template>
