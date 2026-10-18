# DEV-4 — PDF renderer comparison

[Part 10](../System_Plan/10-document-generation-and-approval.md) | [Waiting work](../System_Plan/97-work-while-waiting.md) | [Current handoff](../System_Plan/handoff.md) | [Runner instructions](../experiments/pdf-renderers/README.md)

## Scope and design — 2026-10-18

The owner authorized the next eligible waiting-work item and explicitly requested an updated handoff. C11 remains in force. Compare Dompdf 3.1.6 with Chromium via Playwright 1.64.1, using one fictional certificate layout with six inputs: A4/Legal × standard/long-name/multipage. Every page is stamped “SAMPLE – NOT VALID”. No real template wording, seal, signature, numbering, fee, identity data or office rule is introduced.

The experiment lives under `experiments/pdf-renderers/` with separate package locks and Docker targets. Source application dependencies/configuration/routes remain unchanged. Both candidates receive identical body HTML, one generated DEMO image and the same bundled DejaVu font bytes. Page-number footers use each engine's own API; Chromium's running banner also needs its header API, as explained below. Rendering and QA use no network, read-only root filesystems, a CPU allowance of one and 1 GiB memory per container. They mount only the experiment and dedicated generated-output directories.

Use three fresh-instance/browser repetitions per fixture, then compare page size, Unicode text, long-name wrapping, image embedding, multipage content/header preservation, per-page stamps/footers, embedded fonts, file size and time. The independent QA stage combines Poppler checks/rasterization with pypdf inspection and assembles two sample packs. Review the page PNGs visually before recording a pass. Timing is a local cold-render observation, not a campus SLA or a pooled-browser comparison.

## Results and technical recommendation

**Developer recommendation: use Dompdf 3.1.6 provisionally for simple certificate layouts; retain Chromium as the alternative for templates that require browser layout features.** Both passed this matrix after their print adapters were correct. Dompdf uses the existing PHP execution model and rendered these samples faster, with smaller output files. This chooses the next technical direction; no application PDF package or document workflow was installed. D08/RK12 still require a repeat against real approved templates/fonts and a campus printer before accepting actual output.

Final measured run: **12 fixture/engine combinations, 36 renders, 19 reviewed pages**. Each timing below is the median of three fresh-instance/browser repetitions, in milliseconds. Startup/font/layout/PDF-byte work is included; file writes and browser shutdown are excluded. OS file caches were not cleared. These local measurements are not a campus SLA or a pooled-browser benchmark.

| Fixture | Dompdf pages | Dompdf median ms | Dompdf bytes | Chromium pages | Chromium median ms | Chromium bytes |
|---|---:|---:|---:|---:|---:|---:|
| A4 standard | 1 | 201.04 | 33,264 | 1 | 596.37 | 42,690 |
| A4 long name | 1 | 234.16 | 34,950 | 1 | 573.68 | 44,039 |
| A4 multipage | 3 | 365.54 | 39,310 | 3 | 569.37 | 49,910 |
| Legal standard | 1 | 239.90 | 33,102 | 1 | 559.82 | 42,268 |
| Legal long name | 1 | 208.04 | 34,791 | 1 | 511.60 | 43,549 |
| Legal multipage | 3 | 426.39 | 39,319 | 2 | 587.78 | 48,044 |

Both preserve accented Latin names and all 60 demo rows exactly once, repeat table headers, embed/map the bundled fonts, embed the local DEMO image, keep text within the page, and place the sample stamp and page footer on every page. All 19 page PNGs were inspected: no clipped names/rows, missing glyphs or unintended header/footer overlap remains. The merged packs were checked again for text/fonts, rasterized, and matched their source page PNGs pixel for pixel.

### Findings that affect later work

- A negative-offset fixed banner worked in Dompdf but appeared in Chromium's footer region. Text extraction alone passed because the text still existed. The Chromium adapter now removes only that shared banner through the automation API and renders its text through `headerTemplate`; JavaScript in the HTML remains disabled. QA now checks the stamp's top-margin coordinates on every page. A style-injection attempt stalled under the disabled-script setup; direct DOM removal avoids that wait. The page/action timeout is 15 seconds and each renderer/QA command has a 180-second process limit.
- The initial long-name assertion treated a line break after the existing Santos-Villanueva hyphen as a changed name. Extraction comparison now normalizes that break and Unicode NFC while retaining every character. Visual review confirms the printed wrap is correct.
- Pagination is engine-specific: the same Legal table uses three pages in Dompdf and two in Chromium. Neither loses content, but an approved form's exact pagination must be validated for the chosen engine.
- Dompdf's documented CSS scope does not include flex/grid, and a table row must fit on a page. Keep simple print-specific CSS and bounded rows, then revisit the choice if the actual templates exceed those constraints. [Dompdf limitations](https://github.com/dompdf/dompdf/tree/v3.1.6).
- No physical printing, official font/template, complex-script/CJK coverage, digital signing, PDF/A, accessible-PDF compliance, concurrent-job load or workflow/storage/approval behavior was tested. These are separate scoped acceptance tasks, not implied by successful demo rendering.

### Versions and evidence

The final run used PHP 8.4.26 / Dompdf 3.1.6, Node 24.21.0 / Playwright 1.64.1 / Chromium 156.0.8078.4, pypdf 6.1.3, Pillow 11.3.0 and Poppler 22.12.0. Fonts are DejaVu 2.37 from `fonts-dejavu-core=2.37-6`; identical SHA-256 values were verified in both renderers, and the font licence is bundled in each image.

Observed standalone image sizes: Dompdf 852,640,608 bytes; Chromium 3,627,562,671 bytes; QA 281,393,046 bytes. These include shared layers and are not incremental disk usage. The Chromium candidate uses the official multi-browser test image; a purpose-built production image could be smaller. Full image identities, input/font/output hashes, raw timings and check results are saved in [the dated baseline](../experiments/pdf-renderers/results-2026-10-18.json).

Generated artifacts remain outside Git:

- `output/pdf/dompdf-sample-pack.pdf`: 10 pages, 217,404 bytes.
- `output/pdf/chromium-sample-pack.pdf`: 9 pages, 270,479 bytes.
- `output/pdf/results.json` and `output/pdf/previews/`: full machine results, individual page PNGs and contact sheets.

Pack bookmarks identify fixtures; footer numbering restarts per fixture. The root runner recreates the ignored `tmp/pdfs/` intermediates; this session removes that temporary directory after verification. A later run generates it again.

## Connected modules and verification

No `src/` source or application dependency/configuration was changed (`git diff --exit-code -- src` passed). The isolated Compose configuration resolves to no networking/ports, read-only roots, one CPU, 1 GiB memory, and only experiment/output mounts for all three services. No development database, environment file, credentials, worker or dependency volume is used by the spike. Root ignores keep generated PDFs/previews/intermediates out of Git and app-image build contexts. Exact package pins/locks belong only to the experiment; Composer schema/lock checks retain strict mode while allowing intentional exact constraints.

`./check.ps1` passed **167 tests / 944 assertions**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan level 7. The check restored Vite/worker/scheduler. The saved 136-check browser matrix remains DEV-8 historical evidence; it was not rerun because this slice changes no app screen or route. Syntax/configuration and all 157 local Markdown links across 11 changed documents passed. Final cleanup left all seven development services running, with login returning HTTP 200, no isolated PDF project containers/networks/volumes, and no temporary PDF workspace. The final sample packs remain available.

Future implementation should use a dedicated renderer boundary, private files, input snapshots, template versions, explicit permissions and idempotent queued jobs. Cache approved font metrics and measure concurrency only when that feature is scoped. Current work ends at the technical comparison. **Next: DEV-5, Part 05 design for owner review; implementation waits for approval.**

## References

- [Dompdf 3.1.6 release](https://github.com/dompdf/dompdf/releases/tag/v3.1.6).
- [Dompdf requirements, fonts, resource controls and CSS limitations](https://github.com/dompdf/dompdf/tree/v3.1.6).
- [Playwright PDF options](https://playwright.dev/docs/api/class-page#page-pdf) and [Docker/version guidance](https://playwright.dev/docs/docker).
- [pypdf 6.1.3](https://pypi.org/project/pypdf/6.1.3/) and [Pillow 11.3.0](https://pypi.org/project/Pillow/11.3.0/).
