<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

defineProps<{
    roles: { role: string; label: string; office_name: string | null }[];
}>();
</script>

<template>
    <div class="flex flex-wrap gap-1">
        <Badge
            v-for="role in roles"
            :key="`${role.role}-${role.office_name ?? 'campus'}`"
            variant="secondary"
        >
            {{ role.label }}
            <span v-if="role.office_name" class="text-muted-foreground">
                · {{ role.office_name }}
            </span>
        </Badge>
        <span v-if="roles.length === 0" class="text-sm text-muted-foreground">
            No staff roles
        </span>
    </div>
</template>
