<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineProps<{
    page: Paginated<unknown>;
    noun?: string;
}>();
</script>

<template>
    <div
        v-if="page.total > 0"
        class="flex flex-col items-center justify-between gap-3 text-sm text-muted-foreground sm:flex-row"
    >
        <p>
            Showing {{ page.from }}–{{ page.to }} of {{ page.total }}
            {{ noun ?? 'results' }}
        </p>
        <div class="flex items-center gap-2">
            <Button
                v-if="page.prev_page_url"
                variant="outline"
                size="sm"
                as-child
            >
                <Link :href="page.prev_page_url" preserve-scroll preserve-state>
                    <ChevronLeft />
                    Previous
                </Link>
            </Button>
            <Button v-else variant="outline" size="sm" disabled>
                <ChevronLeft />
                Previous
            </Button>
            <span>Page {{ page.current_page }} of {{ page.last_page }}</span>
            <Button
                v-if="page.next_page_url"
                variant="outline"
                size="sm"
                as-child
            >
                <Link :href="page.next_page_url" preserve-scroll preserve-state>
                    Next
                    <ChevronRight />
                </Link>
            </Button>
            <Button v-else variant="outline" size="sm" disabled>
                Next
                <ChevronRight />
            </Button>
        </div>
    </div>
</template>
