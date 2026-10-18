import { chromium } from '@playwright/test';
import { fixtures, signIn } from './helpers';

export default async function setup() {
    const browser = await chromium.launch();
    try {
        for (const [role, account] of Object.entries(fixtures().accounts)) {
            const context = await browser.newContext({
                baseURL: 'http://web:8000',
            });
            const page = await context.newPage();
            await signIn(page, account);
            await context.storageState({
                path: `storage/browser/auth/${role}.json`,
            });
            await context.close();
        }
    } finally {
        await browser.close();
    }
}
