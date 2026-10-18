import { createHmac, randomUUID } from 'node:crypto';
import { readFileSync } from 'node:fs';
import type { Page } from '@playwright/test';
import { expect } from '@playwright/test';

type Account = { email: string; password: string; totpSecret: string | null };
type Fixtures = {
    accounts: Record<'processor' | 'intake' | 'admin' | 'cashier', Account>;
    historyProfileId: number;
};

export function fixtures(): Fixtures {
    return JSON.parse(
        readFileSync('storage/browser/fixtures.json', 'utf8'),
    ) as Fixtures;
}

export function totp(secret: string): string {
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    const bits = secret
        .split('')
        .map((char) => alphabet.indexOf(char).toString(2).padStart(5, '0'))
        .join('');
    const key = Buffer.from(
        bits.match(/.{8}/g)!.map((byte) => Number.parseInt(byte, 2)),
    );
    const counter = Buffer.alloc(8);
    counter.writeBigUInt64BE(BigInt(Math.floor(Date.now() / 30_000)));
    const hash = createHmac('sha1', key).update(counter).digest();
    const offset = hash[hash.length - 1] & 15;
    return ((hash.readUInt32BE(offset) & 0x7fffffff) % 1_000_000)
        .toString()
        .padStart(6, '0');
}

export async function signIn(page: Page, account: Account) {
    await page.goto('/login');
    await page.getByLabel('Email address', { exact: true }).fill(account.email);
    await page.getByLabel('Password', { exact: true }).fill(account.password);
    await page.getByRole('button', { name: 'Sign in', exact: true }).click();
    if (account.totpSecret) {
        await expect(
            page.getByRole('heading', {
                name: 'Authentication code',
                exact: true,
            }),
        ).toBeVisible();
        await page.getByRole('textbox').fill(totp(account.totpSecret));
        await page
            .getByRole('button', { name: 'Continue', exact: true })
            .click();
    }
    await expect(page).toHaveURL(/\/dashboard$/);
}

export function uniqueClient() {
    const suffix = randomUUID().replaceAll('-', '').slice(0, 12);
    return { name: `Browser ${suffix} (Demo)`, id: `DEMO-B-${suffix}` };
}

export async function createClient(page: Page, client = uniqueClient()) {
    await page.goto('/registrar/clients/create');
    await page.getByLabel('Name', { exact: true }).fill(client.name);
    await page.getByLabel('ID', { exact: true }).fill(client.id);
    await page
        .getByLabel('Source', { exact: true })
        .fill('Browser fixture source (Demo)');
    await page
        .getByLabel('Reason', { exact: true })
        .fill('Browser creation (Demo)');
    await page
        .getByRole('button', { name: 'Create client', exact: true })
        .click();
    await expect(page).toHaveURL(/\/registrar\/clients\/\d+$/);
    await expect(
        page.getByRole('heading', { name: client.name, exact: true }),
    ).toBeVisible();
    return { ...client, url: page.url() };
}

export async function search(page: Page, value: string) {
    await page.goto('/registrar/clients');
    await page.getByRole('searchbox').fill(value);
    await expect(page).toHaveURL(
        (url) => url.searchParams.get('search') === value,
    );
}

export async function selectOption(page: Page, label: string, option: string) {
    await page.getByRole('combobox', { name: label, exact: true }).click();
    await page.getByRole('option', { name: option, exact: true }).click();
}

export async function reasonAction(
    page: Page,
    trigger: string,
    title: string,
    confirm: string,
    reason = 'Browser review (Demo)',
) {
    await page.getByRole('button', { name: trigger, exact: true }).click();
    const dialog = page.getByRole('dialog', { name: title, exact: true });
    await dialog.getByLabel('Reason', { exact: true }).fill(reason);
    await dialog.getByRole('button', { name: confirm, exact: true }).click();
    return dialog;
}

export async function addIdentifier(page: Page, id: string) {
    await page.getByRole('button', { name: 'Add ID', exact: true }).click();
    const dialog = page.getByRole('dialog', {
        name: 'Add an ID to this person',
        exact: true,
    });
    await dialog.getByLabel('ID', { exact: true }).fill(id);
    await dialog
        .getByLabel('Reason', { exact: true })
        .fill('Browser additional ID (Demo)');
    await dialog.getByRole('button', { name: 'Add ID', exact: true }).click();
    return dialog;
}

export async function correct(page: Page, name: string) {
    await page
        .getByRole('button', { name: 'Correct details', exact: true })
        .click();
    const dialog = page.getByRole('dialog', {
        name: 'Correct client details',
        exact: true,
    });
    await dialog.getByLabel('Name', { exact: true }).fill(name);
    await dialog
        .getByLabel('Reason', { exact: true })
        .fill('Browser correction (Demo)');
    await dialog
        .getByRole('button', { name: 'Save correction', exact: true })
        .click();
    return dialog;
}

export async function expectNoOverflow(page: Page) {
    expect(
        await page.evaluate(
            () => document.documentElement.scrollWidth <= window.innerWidth,
        ),
    ).toBe(true);
}
