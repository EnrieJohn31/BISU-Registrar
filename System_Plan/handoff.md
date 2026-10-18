# Handoff

[Project index](index.md) | [Waiting-work list](97-work-while-waiting.md) | [Decisions](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

## Current position — 2026-10-18

**We are in M1 development. DEV-4 (PDF renderer comparison) is complete and DEV-5 (the Part 05 design) is written and waiting for your approval. Do not build Part 05 until you approve it.** No task or PDF renderer is left running. Office-dependent work remains on hold under C11–C14.

Current local branch: `docs/part-05-design-2026-10-18`, based on `spike/pdf-renderers-2026-10-18` (`461ed51`, DEV-4), which sits on the DEV-8 branding branch (`265e695`). Part 03 slice 1 is merged. Part 04 and later waiting-work slices are local, unpushed and not campus-deployed. No push, PR or merge is authorized by this handoff.

### Project status at a glance

| Area | Where it stands | What is next / still gated |
|---|---|---|
| Foundation / Part 03 accounts, roles and audit | Implemented; Part 03 merged (`fa2246a`) | Institutional UAT and campus rollout pending |
| Part 04 slice 1 client records | Implemented locally, QA fixes `896e59e`; full history and audit privacy covered | Official ID/data/privacy/custodian acceptance remain D04/D09 |
| Part 04 imports and account linking | Design only, not implemented | D03/D04/D09 and separately scoped feature work |
| DEV-2 worker, scheduler, Mailpit | Complete locally (`b614386`) | Campus mail/operations decisions still open |
| DEV-3 saved browser regression | Complete locally (`0dc56c6`): 34 cases × four projects | Extend when later features are scoped; physical device acceptance remains D20 |
| DEV-8 BISU shared/browser branding and stray-file cleanup | Complete locally (`265e695`) | Existing local assets and generator retained |
| DEV-4 PDF renderer comparison | Complete locally: 12 combinations, 36 renders, 19 reviewed pages | Provisional Dompdf direction; actual templates/fonts/printer still require D08 acceptance |
| Part 05 services, requirements and fees | Slice 1 design written (DEV-5), not built | **You approve the design (proposed C15)**, then DEV-6 builds the staff catalog on synthetic data |
| Part 07 basic queue | Not implemented | Later scoped design/build task, following the waiting-work sequence; office settings remain open |
| Portal, Registrar and Cashier workflows; document lifecycle/release/reports | Not implemented | Office answers and each part's design/approval gates |
| Campus staging / production | Not deployed | MIS infrastructure, acceptance and release approval |

### Just completed — DEV-5, Part 05 slice 1 design

Written into [Part 05](05-services-requirements-and-fees.md#slice-1-design-catalog-core-dev-5-2026-10-18) for your review. Documentation only: no application code, migration or test changed.

- **Checked against page 115 of the charter** (Enrollment, 5.2.1). It shows the entry header (office, classification, type of transaction, who may avail) and requirements grouped by client situation, each with a "How to Avail" column. It does **not** show the client steps, agency actions, fees, processing time or person responsible. I had wrongly written in [97](97-work-while-waiting.md) that it did; that is corrected, and the step table is deferred to slice 2.
- **Shape proposed:** a service has separately versioned content and fee records; published versions never change and have only a start date; requirements sit in groups the client chooses between, with three kinds (information field, attachment, staff check); fee models are free, fixed and per-unit; money is whole centavos; snapshot and fee-at-endorsement code is ready for Parts 06, 08 and 09.
- **Access:** one new read-only permission, `services.view`, is proposed; services and fees are managed in the Registrar office until D05 says otherwise, so a Cashier supervisor can read but not change them.
- **Demo data:** five "(Demo)" services with fake amounts (11.11 to 44.44), including a grouped-requirements example and a scheduled fee change.
- Seven decisions to approve and eight office questions are listed at the end of the design. A confirmed trade-off: a version published with today's date and found wrong cannot be replaced until tomorrow.

### Previous slice — DEV-4

Compared Dompdf 3.1.6 with Chromium through Playwright 1.64.1 in separate Docker images, using the same fictional body layout and identical bundled DejaVu fonts. Six fixtures cover A4/Legal, standard and long accented names, a local DEMO image and a 60-row multipage table. Every page is stamped “SAMPLE – NOT VALID”. No real template, seal, signature, numbering, fee or identity data was used.

**Provisional technical recommendation: Dompdf for simple certificate layouts.** Its per-fixture medians were 201–426 ms versus 512–596 ms for fresh-browser Chromium in this bounded local run. Chromium remains the alternative for layouts requiring browser CSS. Pagination differs: the Legal table uses three Dompdf pages and two Chromium pages, with all rows preserved. The real template must be checked again; the document module is not implemented by this spike.

Visual review caught Chromium's negative fixed header moving into the footer area. Its adapter now uses the margin-header API; QA checks the top-margin stamp on every page. A line break after the long name's existing hyphen is normalized during extraction comparison. All 19 final page captures are clean, and the two merged packs match the reviewed source pages pixel for pixel.

Detailed method, matrix, limits and provisional decision: [DEV-4 report](../docs/DEV-4-PDF-RENDERERS.md). Reusable harness and dated evidence: [experiment instructions](../experiments/pdf-renderers/README.md), [baseline JSON](../experiments/pdf-renderers/results-2026-10-18.json).

### Validation and artifacts

- PDF matrix: **12 fixture/engine combinations passed**, three repetitions each (**36 renders**), **19 pages visually reviewed**. Paper dimensions, complete Unicode names/rows, repeated headers, per-page stamps/footers, embedded/mapped fonts, local image and page bounds passed. Final packs also passed text/font checks and pixel comparisons.
- `./check.ps1`: **167 tests / 944 assertions passed**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan level 7. Running Vite/worker/scheduler were restored afterwards.
- Application source and dependencies are unchanged by DEV-4 (`git diff --exit-code -- src` passed). PDF packages/locks exist only under `experiments/pdf-renderers/`. Its containers have no network, no ports, read-only roots and only experiment/generated-output mounts; they cannot access the app database or environment.
- The **136-check browser pass is historical DEV-8 evidence**, not a new DEV-4 run. No app page/route changed, so the browser suite was not repeated. Original MySQL race/custodian evidence remains in the dated Part 04 reports.
- Final local artifacts: `output/pdf/dompdf-sample-pack.pdf` (10 pages), `chromium-sample-pack.pdf` (9 pages), `results.json` and `previews/`. They are ignored, synthetic outputs. Bookmarks identify fixtures; page numbers restart per fixture. Temporary `tmp/pdfs/` inputs/source PDFs are removed after verification and recreated by the runner.

Re-run from the repository root:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File ./pdf-spike.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File ./pdf-spike.ps1 -SkipBuild
powershell -NoProfile -ExecutionPolicy Bypass -File ./check.ps1
```

Read the output JSON and inspect new page PNGs before accepting a later run. The runner removes its previous packs/result manifest first, refreshes only its named previews and cleans up only `bisu-registrar-pdf-spike`. A failed run cannot present an old success manifest. Renderer/QA processes are capped at 180 seconds; Chromium page/action waits are capped at 15 seconds.

### Next action

1. **You review the [Part 05 design](05-services-requirements-and-fees.md#slice-1-design-catalog-core-dev-5-2026-10-18)** and approve, change or reject the seven proposals (including the new `services.view` permission). On approval I record it as decision C15, then build DEV-6 on a new local branch with `./check.ps1`, MySQL race probes and new browser cases. Nothing in Part 05 is built before then.
2. **Alternative or parallel:** DEV-7 (basic queue) needs its own short design and approval first. Thesis and office-preparation tasks in [97](97-work-while-waiting.md) need no code.
3. **Send the [requirements checklist](requirements.pdf)** if not yet sent. The rest of the Citizen's Charter (steps, fees, processing times and the Cashier section) is item 1 and unlocks Part 05 slice 2.
4. Record incoming office answers; they do not automatically authorize a previously gated feature. No messages, appointments or office permissions were sent or obtained by this slice.

### Office decisions still needed

| Decisions | Owner | Still needed |
|---|---|---|
| D03 / D04 / D09 | Registrar + MIS + DPO | Linking/recovery proof, official IDs, approved import source/reviewer/correction authority, permitted fields, privacy/retention/access auditing |
| **C15 (proposed)** | Project owner | Approve, change or reject the seven Part 05 slice 1 proposals, including `services.view` |
| D05 / D07 | Registrar + Cashier | Approved services/requirements, fees, financial rules and complete charter source; the eight questions in the Part 05 design |
| D08 | Registrar | Approved templates/fonts, signatories, signatures, numbering, release rules and actual output/printer acceptance |
| D11 / D13 / D20 | MIS + offices | Campus mail/assisted delivery, workload/operational targets, supported devices/browsers |
| D02 / D14 | MIS + sponsor + offices | Campus infrastructure, staging/release owners and deployment approval |

### Runtime and watch-outs

- Development URLs: app http://localhost:8085, Vite http://localhost:5178, captured-email inbox http://localhost:8026. Seven dev services remain available. This is not campus production.
- Keep real records, private documents, secrets, runtime data and backups out of Git. Preserve actual human commit authorship and the existing attribution/push guards. Local commits only; publishing needs an explicit request.
- Existing registration-disabled builds fail because `Register.vue` imports a route omitted by generation in that configuration. Record a separate technical fix before using that setting; this slice did not change registration policy.
- Dompdf needs print-specific CSS; flex/grid and oversized table rows need reconsideration. Future real templates, complex scripts, signing/PDF standards, printer behavior, private storage, approval/versioning and queued retry/idempotency require their own design/acceptance.
- Font-metric caching and browser reuse may improve future throughput, but this spike measures fresh instances. Profile real workload before choosing operational limits. Restart long-running worker/scheduler after PHP job/config changes and keep job timeout below reservation expiry.
- For Part 05, enforce the office in the policy (the shared Office supervisor role lists `services.manage` and `fees.manage` for both offices), give composite index names explicitly (MySQL's 64-character limit failed in Part 04), compare JSON in tests with `assertEquals`, and keep PHP and TypeScript permission lists in step.
