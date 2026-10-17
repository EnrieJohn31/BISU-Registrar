import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Permission } from '@/types';

/**
 * Show or hide UI by permission. The server still checks every request.
 */
export function usePermissions() {
    const page = usePage();
    const granted = computed(() => new Set(page.props.auth.permissions ?? []));

    const can = (permission: Permission): boolean =>
        granted.value.has(permission);

    return { can };
}
