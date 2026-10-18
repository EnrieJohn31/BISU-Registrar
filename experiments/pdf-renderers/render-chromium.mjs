import { createHash } from 'node:crypto';
import { readFile, readdir, writeFile } from 'node:fs/promises';
import { performance } from 'node:perf_hooks';
import { chromium } from '/opt/renderer/node_modules/playwright/index.mjs';

const fixtures = JSON.parse(await readFile('/work/fixtures.json', 'utf8'));
const repeats = Number(process.env.PDF_SPIKE_REPEATS || 3);
if (!Number.isInteger(repeats) || repeats < 1 || repeats > 5) throw new Error('Expected one to five repetitions.');
const results = [];
let version;
for (const fixture of fixtures) {
    const times = [];
    for (let iteration = 0; iteration < repeats; iteration++) {
        const started = performance.now();
        const browser = await chromium.launch({ args: ['--allow-file-access-from-files'] });
        try {
            version = browser.version();
            const context = await browser.newContext({ javaScriptEnabled: false });
            context.setDefaultTimeout(15_000);
            context.setDefaultNavigationTimeout(15_000);
            await context.route('**/*', async (route) => {
                if (new URL(route.request().url()).protocol !== 'file:') throw new Error('Unexpected remote resource.');
                await route.continue();
            });
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', (error) => errors.push(error.message));
            page.on('requestfailed', (request) => errors.push(request.url()));
            await page.goto(`file:///work/html/${fixture.id}.html`);
            await page.emulateMedia({ media: 'print' });
            // Chromium positions a negative fixed header differently from Dompdf.
            // Keep the shared body, and put its running banner in Chromium's margin API.
            await page.evaluate(() => document.querySelector('.banner')?.remove());
            await page.evaluate(() => document.fonts.ready);
            if (errors.length) throw new Error(errors.join('\n'));
            const pdf = await page.pdf({
                format: fixture.paper, preferCSSPageSize: true, printBackground: true,
                displayHeaderFooter: true,
                headerTemplate: `<div style="width:100%;margin:0 18mm;padding-bottom:7pt;border-bottom:1px solid #ab2946;font-family:DejaVu Sans;font-size:9pt;color:#ab2946"><strong>SAMPLE – NOT VALID</strong><span style="float:right;font-size:7pt">${fixture.paper} / ${fixture.variant} (Demo)</span></div>`,
                footerTemplate: '<div style="width:100%;text-align:center;font-family:DejaVu Sans;font-size:8pt;color:#546e82">Chromium (Demo) | Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>',
            });
            times.push(Number((performance.now() - started).toFixed(2)));
            await writeFile(`/work/chromium/${fixture.id}.pdf`, pdf);
        } finally {
            await browser.close();
        }
    }
    results.push({ id: fixture.id, milliseconds: times });
    console.log(`Chromium: ${fixture.id} ${JSON.stringify(times)} ms`);
}
const fonts = {};
for (const name of (await readdir('/opt/pdf-fonts')).filter((name) => name.endsWith('.ttf')).sort()) {
    fonts[name] = createHash('sha256').update(await readFile(`/opt/pdf-fonts/${name}`)).digest('hex');
}
await writeFile('/work/chromium/metrics.json', JSON.stringify({
    engine: 'chromium', playwright: '1.64.1', version, node: process.version, repeats,
    font_sha256: fonts,
    timing_scope: 'Fresh browser per repetition; browser launch, HTML/font load through PDF bytes, file write/close excluded.',
    fixtures: results,
}, null, 2));
