# DEV-4: isolated PDF renderer comparison

This experiment uses made-up certificate wording and labelled synthetic data. It does not implement Part 10, connect to Laravel or use an official template, seal, signature, fee or ID format.

Candidates: Dompdf 3.1.6 and Chromium through Playwright 1.64.1. Composer/npm locks are independent of `src/`. Both images bundle the same DejaVu font files and font licence. The QA image uses pinned pypdf/Pillow and Poppler from Debian Bookworm.

From the repository root with Docker available:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File ./pdf-spike.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File ./pdf-spike.ps1 -SkipBuild
```

The first build downloads packages/images. All fixture creation, rendering and verification run afterwards with `network_mode: none`, read-only root filesystems, one CPU and a 1 GiB memory limit per container. Only this directory (read-only), `tmp/pdfs/` and `output/pdf/` are mounted. No development app, network, dependency volume, environment file, database or credentials are used. The root runner cleans up only its fixed `bisu-registrar-pdf-spike` project. It removes its two named packs and result manifest before a new run; QA refreshes only its named page PNGs. A failed run cannot leave an old success manifest as the current result.

Six inputs cover standard, long-name and multipage content at A4 and Legal sizes. Each page must carry “SAMPLE – NOT VALID”. Names include accented Latin characters. The multipage fixture has 60 uniquely labelled demo rows with a repeating table header. A generated geometric DEMO image exercises local image embedding. Both engines read the same generated body HTML. Page-number footers use each engine's API. Chromium also hides the shared negative-offset fixed banner and renders that same text through its margin-header API; visual review found that its fixed-header positioning differs from Dompdf.

Three repetitions per fixture use a fresh Dompdf instance/font cache or a fresh Chromium browser. Metrics include construction/startup, font/layout work and PDF bytes, excluding the file write and browser close. This is a bounded local cold-render comparison, not a pooled-browser benchmark or campus workload target. Timing samples are recorded individually; repeat count is configurable from 1 to 5.

Outputs (ignored by Git):

- `output/pdf/dompdf-sample-pack.pdf` and `chromium-sample-pack.pdf`: mixed-paper packs, with bookmarks and per-fixture page numbering.
- `output/pdf/results.json`: versions, input/font hashes, timing samples and structural/content checks.
- `output/pdf/previews/`: Poppler page PNGs and contact sheets. Automated success still requires human visual review.
- `tmp/pdfs/`: generated HTML, source PDFs and intermediate text checks, safe to remove after reviewing this run.

QA checks paper dimensions, expected page counts, all names/rows, per-page stamps/footers, repeated headers, embedded fonts/Unicode mappings, page bounds and successful rasterization. Packs are assembled only after the source documents pass; their extracted page text and embedded fonts are checked again. Do not infer layout correctness from text extraction alone.

The dated [design, result and decision record](../../docs/DEV-4-PDF-RENDERERS.md) states the recommendation and remaining limits. Re-run with office-approved source templates/fonts and a campus printer when D08 is answered, before accepting production output. Future implementation must own private storage, permissions, versioning, approvals and retry/idempotency; none is added here.
