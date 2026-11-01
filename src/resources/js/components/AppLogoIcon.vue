<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
    // The 320px derivative, for panels that show the seal above 96 pixels.
    large?: boolean;
};

defineProps<Props>();
</script>

<template>
    <img
        :src="
            large ? '/branding/bisu-seal-large.png' : '/branding/bisu-seal.png'
        "
        alt="BISU seal"
        :width="large ? 320 : 96"
        :height="large ? 320 : 96"
        :class="className"
        v-bind="$attrs"
    />
</template>
