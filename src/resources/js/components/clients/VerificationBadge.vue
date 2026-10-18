<script setup lang="ts">
import { BadgeCheck, CircleHelp, TriangleAlert } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import type { VerificationStateValue } from '@/types';

defineProps<{
    state: VerificationStateValue;
    label: string;
}>();
</script>

<template>
    <Badge
        v-if="state === 'verified'"
        variant="outline"
        class="border-emerald-600/40 text-emerald-700 dark:text-emerald-400"
    >
        <BadgeCheck /> {{ label }}
    </Badge>
    <Badge
        v-else-if="state === 'needs_review'"
        variant="outline"
        class="border-amber-600/50 text-amber-700 dark:text-amber-400"
    >
        <TriangleAlert /> {{ label }}
    </Badge>
    <Badge v-else variant="secondary"> <CircleHelp /> {{ label }} </Badge>
</template>
