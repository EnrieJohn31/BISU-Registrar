<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/branding/bisu-seal.png"
        alt="BISU seal"
        width="96"
        height="96"
        :class="className"
        v-bind="$attrs"
    />
</template>
