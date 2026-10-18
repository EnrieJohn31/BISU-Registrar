import { mkdirSync } from 'node:fs';
import { test, expect } from './fixtures';
import {
    addIdentifier,
    correct,
    createClient,
    expectNoOverflow,
    fixtures,
    reasonAction,
    search,
    selectOption,
    uniqueClient,
} from './helpers';

test('B01 processor dashboard opens client records', async ({ page }) => {
    await page.goto('/dashboard');
    await page
        .getByRole('link', { name: 'Client records', exact: true })
        .last()
        .click();
    await expect(
        page.getByRole('heading', { name: 'Client records', exact: true }),
    ).toBeVisible();
});

test('B02 list masks full IDs and shows record counts', async ({ page }) => {
    await search(page, 'DEMO-S-0001');
    await expect(page.locator('tbody')).toContainText('Juan Dela Cruz (Demo)');
    await expect(page.locator('tbody')).toContainText('•••••••0001');
    await expect(page.locator('tbody')).not.toContainText('DEMO-S-0001');
    await expect(
        page.getByText(/Showing 1–1 of 1 client records/),
    ).toBeVisible();
});

test('B03 shared names remain separate search matches', async ({ page }) => {
    await search(page, 'reyes');
    await expect(page.locator('tbody tr')).toHaveCount(3);
    await expect(
        page.getByRole('link', { name: 'Alex Reyes (Demo)', exact: true }),
    ).toHaveCount(2);
});

test('B04 ID prefix search finds alumni identifiers', async ({ page }) => {
    await search(page, 'DEMO-A');
    await expect(page.locator('tbody tr')).toHaveCount(6);
    await expect(page.locator('tbody')).toContainText('Rosa Villanueva (Demo)');
});

test('B05 leading-zero ID search preserves text', async ({ page }) => {
    await search(page, '00123456');
    await expect(page.locator('tbody tr')).toHaveCount(1);
    await page
        .getByRole('link', { name: 'Leading Zero Case (Demo)', exact: true })
        .click();
    await expect(page.getByRole('table')).toContainText('00123456');
});

test('B06 Unicode names remain searchable', async ({ page }) => {
    await search(page, 'zoë');
    await expect(
        page.getByRole('link', { name: 'Zoë Peña (Demo)', exact: true }),
    ).toBeVisible();
});

test('B07 empty search result is explained', async ({ page }) => {
    await search(page, 'No-such-browser-client');
    await expect(
        page.getByText('No client records match these filters.', {
            exact: false,
        }),
    ).toBeVisible();
});

test('B08 category filter includes and excludes the same record', async ({
    page,
}) => {
    await search(page, 'Rosa Villanueva');
    await selectOption(page, 'Category', 'Student and alumni');
    await expect(page).toHaveURL(
        (url) => url.searchParams.get('category') === 'both',
    );
    await expect(page.locator('tbody')).toContainText('Rosa Villanueva (Demo)');
    await selectOption(page, 'Category', 'Student');
    await expect(
        page.getByText('No client records match these filters.', {
            exact: false,
        }),
    ).toBeVisible();
});

test('B09 verification filter selects the review case', async ({ page }) => {
    await search(page, 'Alex Reyes');
    await selectOption(page, 'Verification', 'Needs review');
    await expect(page).toHaveURL(
        (url) => url.searchParams.get('state') === 'needs_review',
    );
    await expect(page.locator('tbody tr')).toHaveCount(1);
    await expect(page.locator('tbody')).toContainText('Needs review');
});

test('B10 detail exposes the authorized full ID and provenance', async ({
    page,
}) => {
    await search(page, 'DEMO-S-0001');
    await page
        .getByRole('link', { name: 'Juan Dela Cruz (Demo)', exact: true })
        .click();
    await expect(page.getByRole('table')).toContainText('DEMO-S-0001');
    await expect(page.locator('dl')).toContainText(
        'Demo data, not a real person',
    );
    await expect(page.locator('#client-history')).toContainText(
        'Profile created',
    );
});

test('B11 one person may have student and alumni IDs', async ({ page }) => {
    await search(page, 'Rosa Villanueva');
    await page
        .getByRole('link', { name: 'Rosa Villanueva (Demo)', exact: true })
        .click();
    await expect(page.getByRole('table')).toContainText('DEMO-S-0101');
    await expect(page.getByRole('table')).toContainText('DEMO-A-0101');
});

test('B12 retired IDs remain visible in history', async ({ page }) => {
    await search(page, 'DEMO-S-0401');
    await page
        .getByRole('link', { name: 'Retired ID Case (Demo)', exact: true })
        .click();
    await expect(
        page.getByRole('row').filter({ hasText: 'DEMO-S-0401' }),
    ).toContainText('Retired');
    await expect(
        page.getByRole('row').filter({ hasText: 'DEMO-S-0402' }),
    ).toContainText('Current');
});

test('B13 list pagination reaches and returns from the next page', async ({
    page,
}) => {
    await page.goto('/registrar/clients');
    const firstLink = page.locator('tbody tr').first().getByRole('link');
    const firstHref = await firstLink.getAttribute('href');
    expect(firstHref).toBeTruthy();
    await expect(page.locator('tbody tr')).toHaveCount(15);
    await page.getByRole('link', { name: 'Next', exact: true }).click();
    await expect(page).toHaveURL((url) => url.searchParams.get('page') === '2');
    await expect(firstLink).not.toHaveAttribute('href', firstHref!);
    await page.getByRole('link', { name: 'Previous', exact: true }).click();
    await expect(firstLink).toHaveAttribute('href', firstHref!);
});

test('B14 create records the first ID and creation history', async ({
    page,
}) => {
    const client = await createClient(page);
    await expect(page.getByRole('table')).toContainText(client.id);
    await expect(page.locator('#client-history')).toContainText(
        'Browser creation (Demo)',
    );
});

test('B15 case-insensitive duplicate IDs keep form values', async ({
    page,
}) => {
    const client = uniqueClient();
    await page.goto('/registrar/clients/create');
    await page.getByLabel('Name', { exact: true }).fill(client.name);
    await page.getByLabel('ID', { exact: true }).fill('demo-s-0001');
    await page
        .getByLabel('Source', { exact: true })
        .fill('Browser source (Demo)');
    await page
        .getByLabel('Reason', { exact: true })
        .fill('Duplicate probe (Demo)');
    await page
        .getByRole('button', { name: 'Create client', exact: true })
        .click();
    await expect(
        page.getByText('This ID is already recorded', { exact: false }),
    ).toBeVisible();
    await expect(page.getByLabel('Name', { exact: true })).toHaveValue(
        client.name,
    );
    await expect(page.getByLabel('ID', { exact: true })).toHaveValue(
        'demo-s-0001',
    );
});

test('B16 shared new names never merge profiles', async ({ page }) => {
    const first = await createClient(page);
    const second = await createClient(page, {
        name: first.name,
        id: uniqueClient().id,
    });
    expect(second.url).not.toBe(first.url);
    await search(page, first.name);
    await expect(
        page.getByRole('link', { name: first.name, exact: true }),
    ).toHaveCount(2);
});

test('B17 correction retains old and new names', async ({ page }) => {
    const client = await createClient(page);
    const name = `Corrected ${client.name}`;
    await expect(await correct(page, name)).toBeHidden();
    await expect(
        page.getByRole('heading', { name, exact: true }),
    ).toBeVisible();
    await expect(page.locator('#client-history')).toContainText(client.name);
    await expect(page.locator('#client-history')).toContainText(name);
});

test('B18 no-op correction is refused', async ({ page }) => {
    const client = await createClient(page);
    const dialog = await correct(page, client.name);
    await expect(
        dialog.getByText('Nothing was changed.', { exact: false }),
    ).toBeVisible();
});

test('B19 verification records actor and source reason', async ({ page }) => {
    await createClient(page);
    await expect(
        await reasonAction(
            page,
            'Verify',
            'Verify this client',
            'Verify',
            'Browser verified source (Demo)',
        ),
    ).toBeHidden();
    await expect(page.locator('dl')).toContainText('Verified');
    await expect(page.locator('dl')).toContainText('Processor Browser (Demo)');
    await expect(page.locator('#client-history')).toContainText(
        'Browser verified source (Demo)',
    );
});

test('B20 correcting a verified client warns and returns to review', async ({
    page,
}) => {
    const client = await createClient(page);
    await expect(
        await reasonAction(page, 'Verify', 'Verify this client', 'Verify'),
    ).toBeHidden();
    await page
        .getByRole('button', { name: 'Correct details', exact: true })
        .click();
    const dialog = page.getByRole('dialog', {
        name: 'Correct client details',
        exact: true,
    });
    await expect(dialog).toContainText('returns to “Needs review”');
    await dialog
        .getByLabel('Name', { exact: true })
        .fill(`Corrected ${client.name}`);
    await dialog
        .getByLabel('Reason', { exact: true })
        .fill('Verified correction (Demo)');
    await dialog
        .getByRole('button', { name: 'Save correction', exact: true })
        .click();
    await expect(dialog).toBeHidden();
    await expect(page.locator('dl')).toContainText('Needs review');
});

test('B21 flagging preserves the person and reason', async ({ page }) => {
    const client = await createClient(page);
    await expect(
        await reasonAction(
            page,
            'Flag for review',
            'Flag for review',
            'Flag for review',
        ),
    ).toBeHidden();
    await expect(
        page.getByRole('heading', { name: client.name, exact: true }),
    ).toBeVisible();
    await expect(page.locator('dl')).toContainText('Needs review');
    await expect(page.locator('#client-history')).toContainText(
        'Browser review (Demo)',
    );
});

test('B22 add an alumni ID to an existing person', async ({ page }) => {
    const client = await createClient(page);
    const id = uniqueClient().id;
    await expect(await addIdentifier(page, id)).toBeHidden();
    await expect(page.getByRole('table')).toContainText(id);
    await expect(page.getByRole('table')).toContainText(client.id);
});

test('B23 an existing alumni ID cannot be reassigned', async ({ page }) => {
    await createClient(page);
    const dialog = await addIdentifier(page, 'demo-a-0001');
    await expect(
        dialog.getByText('This ID is already recorded', { exact: false }),
    ).toBeVisible();
});

test('B24 retiring an old ID preserves it as history', async ({ page }) => {
    const client = await createClient(page);
    await expect(await addIdentifier(page, uniqueClient().id)).toBeHidden();
    await page
        .getByRole('row')
        .filter({ hasText: client.id })
        .getByRole('button', { name: 'Retire', exact: true })
        .click();
    const dialog = page.getByRole('dialog', {
        name: `Retire ${client.id}`,
        exact: true,
    });
    await dialog
        .getByLabel('Reason', { exact: true })
        .fill('Browser retired ID (Demo)');
    await dialog
        .getByRole('button', { name: 'Retire ID', exact: true })
        .click();
    await expect(dialog).toBeHidden();
    await expect(
        page.getByRole('row').filter({ hasText: client.id }),
    ).toContainText('Retired');
});

test('B25 the last current ID cannot be retired', async ({ page }) => {
    const client = await createClient(page);
    const dialog = await reasonAction(
        page,
        'Retire',
        `Retire ${client.id}`,
        'Retire ID',
    );
    await expect(
        dialog.getByText('A profile must keep at least one current ID.', {
            exact: false,
        }),
    ).toBeVisible();
});

test('B26 a stale correction refreshes instead of overwriting', async ({
    page,
    context,
}) => {
    const client = await createClient(page);
    await page
        .getByRole('button', { name: 'Correct details', exact: true })
        .click();
    const dialog = page.getByRole('dialog', {
        name: 'Correct client details',
        exact: true,
    });
    await dialog
        .getByLabel('Name', { exact: true })
        .fill(`Stale ${client.name}`);
    await dialog
        .getByLabel('Reason', { exact: true })
        .fill('Stale browser probe (Demo)');
    const other = await context.newPage();
    await other.goto(client.url);
    await expect(await correct(other, `Latest ${client.name}`)).toBeHidden();
    await dialog
        .getByRole('button', { name: 'Save correction', exact: true })
        .click();
    await expect(
        dialog.getByText('This client profile was changed by someone else.', {
            exact: false,
        }),
    ).toBeVisible();
    await expect(dialog.getByLabel('Name', { exact: true })).toHaveValue(
        `Latest ${client.name}`,
    );
    await other.close();
});

test.describe('Intake access', () => {
    test.use({ storageState: 'storage/browser/auth/intake.json' });

    test('B27 Intake sees full details without change buttons', async ({
        page,
    }) => {
        await search(page, 'DEMO-S-0001');
        await expect(
            page.getByRole('link', { name: 'New client', exact: true }),
        ).toHaveCount(0);
        await page
            .getByRole('link', { name: 'Juan Dela Cruz (Demo)', exact: true })
            .click();
        await expect(
            page.getByText('View only', { exact: true }),
        ).toBeVisible();
        await expect(page.getByRole('table')).toContainText('DEMO-S-0001');
        for (const name of [
            'Correct details',
            'Add ID',
            'Verify',
            'Flag for review',
            'Retire',
        ]) {
            await expect(
                page.getByRole('button', { name, exact: true }),
            ).toHaveCount(0);
        }
    });

    test('B28 Intake create URL is denied by the server', async ({ page }) => {
        const response = await page.goto('/registrar/clients/create');
        expect(response?.status()).toBe(403);
    });
});

test('B29 client reasons remain outside the general audit viewer', async ({
    page,
    browser,
}) => {
    const client = await createClient(page);
    const reason = `Review ${client.name} ${client.id}`;
    await expect(
        await reasonAction(
            page,
            'Flag for review',
            'Flag for review',
            'Flag for review',
            reason,
        ),
    ).toBeHidden();
    await expect(page.locator('#client-history')).toContainText(reason);
    const context = await browser.newContext({
        baseURL: 'http://web:8000',
        storageState: 'storage/browser/auth/admin.json',
    });
    try {
        const audit = await context.newPage();
        const response = await audit.goto(
            '/admin/audit?action=client.flagged_for_review',
        );
        expect(response?.status()).toBe(200);
        const html = await response!.text();
        expect(html).not.toContain(client.name);
        expect(html).not.toContain(client.id);
        await expect(audit.locator('tbody')).toContainText(
            'Reason recorded in restricted client history',
        );
        await audit.locator('tbody tr').first().click();
        await expect(audit.getByRole('dialog')).not.toContainText(client.name);
        await expect(audit.getByRole('dialog')).not.toContainText(client.id);
    } finally {
        await context.close();
    }
});

test.describe('Cashier access after actual two-factor sign-in', () => {
    test.use({ storageState: 'storage/browser/auth/cashier.json' });

    test('B30 Cashier has no client navigation and cannot open records', async ({
        page,
    }) => {
        await page.goto('/dashboard');
        await expect(
            page.getByRole('link', { name: 'Client records', exact: true }),
        ).toHaveCount(0);
        const response = await page.goto('/registrar/clients');
        expect(response?.status()).toBe(403);
    });
});

test('B31 full history pages reach creation and return to the newest revision', async ({
    page,
}, testInfo) => {
    await page.goto(`/registrar/clients/${fixtures().historyProfileId}`);
    const history = page.locator('#client-history');
    await expect(history.locator('ol > li')).toHaveCount(50);
    await expect(history.locator('ol > li').first()).toContainText(
        'version 51',
    );
    await history.evaluate((element) =>
        element.scrollIntoView({ block: 'start' }),
    );
    mkdirSync('/artifacts/screenshots', { recursive: true });
    await page.screenshot({
        path: `/artifacts/screenshots/${testInfo.project.name}-history-newest.png`,
    });
    await history.getByRole('link', { name: 'Next', exact: true }).click();
    await expect(page).toHaveURL(
        (url) => url.searchParams.get('history_page') === '2',
    );
    await expect(history.locator('ol > li')).toHaveCount(1);
    await expect(history).toContainText('version 1');
    await expect(history).toContainText('Browser history creation (Demo)');
    await expect(
        history.getByRole('button', { name: 'Next', exact: true }),
    ).toBeDisabled();
    await expectNoOverflow(page);
    await history.evaluate((element) =>
        element.scrollIntoView({ block: 'start' }),
    );
    await page.screenshot({
        path: `/artifacts/screenshots/${testInfo.project.name}-history-oldest.png`,
    });
    await history.getByRole('link', { name: 'Previous', exact: true }).click();
    await expect(history.locator('ol > li')).toHaveCount(50);
});

test('B32 long names stay inside the list layout', async ({
    page,
}, testInfo) => {
    await search(page, 'Santos-Villanueva');
    if (testInfo.project.name.endsWith('-dark')) {
        await expect(page.locator('html')).toHaveClass(/dark/);
    } else {
        await expect(page.locator('html')).not.toHaveClass(/dark/);
    }
    await expect(page.locator('tbody')).toContainText(
        'Maria Concepcion Magdalena',
    );
    await expectNoOverflow(page);
    mkdirSync('/artifacts/screenshots', { recursive: true });
    await page.screenshot({
        path: `/artifacts/screenshots/${testInfo.project.name}-list.png`,
    });
});

test('B33 detail layout has no page-level horizontal overflow', async ({
    page,
}, testInfo) => {
    await search(page, 'Rosa Villanueva');
    await page
        .getByRole('link', { name: 'Rosa Villanueva (Demo)', exact: true })
        .click();
    await expect(
        page.getByRole('heading', {
            name: 'Rosa Villanueva (Demo)',
            exact: true,
        }),
    ).toBeVisible();
    await expect(page.getByRole('table')).toContainText('DEMO-A-0101');
    await expectNoOverflow(page);
    mkdirSync('/artifacts/screenshots', { recursive: true });
    await page.screenshot({
        path: `/artifacts/screenshots/${testInfo.project.name}-detail.png`,
    });
});

test('B34 correction dialog cancels with Escape and restores keyboard focus', async ({
    page,
}) => {
    await createClient(page);
    const trigger = page.getByRole('button', {
        name: 'Correct details',
        exact: true,
    });
    await trigger.click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.getByRole('dialog')).toBeHidden();
    await expect(trigger).toBeFocused();
});
