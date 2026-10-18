import type { Page } from '@playwright/test';
import { test as base, expect } from '@playwright/test';

// Every test gets a fresh browser context; a page exception is always a failure.
export const test = base.extend<{ pageErrors: string[] }>({
    pageErrors: [
        async ({ page, context }, use) => {
            const errors: string[] = [];
            const observe = (target: Page) => {
                target.on('pageerror', (error) => errors.push(error.message));
                target.on('console', (message) => {
                    // Direct-URL denial cases intentionally produce a 403 resource error.
                    if (
                        message.type() === 'error' &&
                        !/^Failed to load resource:.*403/.test(message.text())
                    ) {
                        errors.push(message.text());
                    }
                });
            };
            observe(page);
            context.on('page', observe);
            await use(errors);
            expect(errors, 'Unexpected browser page exceptions').toEqual([]);
        },
        { auto: true },
    ],
});
export { expect } from '@playwright/test';
