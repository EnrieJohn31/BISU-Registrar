<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { Mail } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import type { RouteDefinition } from '@/wayfinder';

// Development-only (C22): the server sends this component an inbox link only
// when APP_ENV=local, so campus servers never show it.
const props = defineProps<{
    action: RouteDefinition<'post'>;
    inboxUrl: string;
}>();

const http = useHttp();
const status = ref<'idle' | 'sent' | 'failed'>('idle');

const send = async (): Promise<void> => {
    try {
        await http.submit(props.action);
        status.value = 'sent';
    } catch {
        status.value = 'failed';
    }
};
</script>

<template>
    <div
        class="w-full space-y-2 rounded-lg border border-dashed border-border p-3 text-left text-sm"
    >
        <p class="font-medium">Development only</p>
        <p class="text-muted-foreground">
            No authenticator app? Send the current code to the test inbox.
        </p>
        <Button
            type="button"
            variant="outline"
            size="sm"
            :disabled="http.processing"
            data-test="dev-two-factor-email"
            @click="send"
        >
            <Mail />Email me the code
        </Button>
        <p role="status" aria-live="polite">
            <template v-if="status === 'sent'">
                Sent. Open the
                <a
                    :href="inboxUrl"
                    target="_blank"
                    rel="noopener"
                    class="underline underline-offset-4"
                    >test inbox</a
                >
                on this PC. The code changes every 30 seconds.
            </template>
            <span v-else-if="status === 'failed'" class="text-destructive">
                The code could not be sent. Sign in again and retry.
            </span>
        </p>
    </div>
</template>
