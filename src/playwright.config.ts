import { defineConfig } from '@playwright/test';

if (process.env.BISU_BROWSER_TESTS !== '1') {
    throw new Error('Run the isolated suite with ./browser-tests.ps1.');
}

export default defineConfig({
    testDir: './tests/Browser',
    globalSetup: './tests/Browser/auth.setup.ts',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    timeout: 30_000,
    expect: { timeout: 7_000 },
    forbidOnly: true,
    outputDir: '/artifacts/test-results',
    reporter: [
        ['list'],
        ['html', { outputFolder: '/artifacts/report', open: 'never' }],
        ['json', { outputFile: '/artifacts/results.json' }],
    ],
    use: {
        baseURL: 'http://web:8000',
        browserName: 'chromium',
        storageState: 'storage/browser/auth/processor.json',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: ['light', 'dark'].flatMap((colorScheme) => [
        {
            name: `desktop-${colorScheme}`,
            use: {
                viewport: { width: 1440, height: 900 },
                colorScheme: colorScheme as 'light' | 'dark',
            },
        },
        {
            name: `phone-${colorScheme}`,
            use: {
                viewport: { width: 390, height: 844 },
                isMobile: true,
                hasTouch: true,
                colorScheme: colorScheme as 'light' | 'dark',
            },
        },
    ]),
});
