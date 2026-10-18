# Handoff

[Project index](index.md) | [Waiting-work list](97-work-while-waiting.md) | [Decisions](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

## Current position — 2026-10-18

**M1 release handoff: Part 04 and the completed waiting-work slices are included in [PR #11](https://github.com/EnrieJohn31/BISU-Registrar/pull/11), under the owner's explicit commit/push/main-merge authorization (C18).** GitHub records the final head, checks and merge result. Campus deployment remains pending. Next development still needs approval of the Part 05 design; publishing it does not approve implementation.

Release source branch: `release/part04-and-waiting-work-2026-10-18`, including all 20 previously local commits through `358583f`, plus this release record. Use the [release report](../docs/RELEASE-2026-10-18.md) and PR for publication evidence; use up-to-date `main` after publication. Preserve human authorship and protected-main checks. C18 applies to this release only; later publication still needs an explicit request.

Daily phone access: start Docker Desktop and double-click `start-lan.cmd` (or run `./lan-preview.ps1`); it detects the LAN IP and prints links. The owner confirmed the physical phone login page opens. OPS-1 still needs sign-in/navigation, device/browser details and a second-laptop check. Office gates remain unchanged.

### Project status at a glance

| Area | Where it stands | What is next / still gated |
|---|---|---|
| Foundation / Part 03 accounts, roles and audit | Implemented; Part 03 merged (`fa2246a`) | Institutional UAT and campus rollout pending |
| Part 04 slice 1 client records | Implemented and included in release PR #11, QA fixes `896e59e`; full history and audit privacy covered | Official ID/data/privacy/custodian acceptance remain D04/D09 |
| Part 04 imports and account linking | Design only, not implemented | D03/D04/D09 and separately scoped feature work |
| DEV-2 worker, scheduler, Mailpit | Complete (`b614386`), included in release PR #11 | Campus mail/operations decisions still open |
| DEV-3 saved browser regression | Complete (`0dc56c6`): 34 cases × four projects, included in release PR #11 | Extend when later features are scoped; physical device acceptance remains D20 |
| DEV-8 BISU shared/browser branding and stray-file cleanup | Complete (`265e695`), included in release PR #11 | Existing local assets and generator retained |
| DEV-4 PDF renderer comparison | Complete: 12 combinations, 36 renders, 19 reviewed pages; experiment included in PR #11 | Provisional Dompdf direction; actual templates/fonts/printer still require D08 acceptance |
| OPS-1 same-network phone preparation | Optional LAN preview implemented locally; PC HTTP/assets and stop/restore checked; owner confirmed physical phone login page opens | Still needed: phone sign-in/navigation, model/OS/browser versions and second-laptop access |
| Part 05 services, requirements and fees | Slice 1 design written (DEV-5), not built | **You approve the design (proposed C15)**, then DEV-6 builds the staff catalog on synthetic data |
| Part 07 basic queue | Not implemented | Later scoped design/build task, following the waiting-work sequence; office settings remain open |
| Portal, Registrar and Cashier workflows; document lifecycle/release/reports | Not implemented | Office answers and each part's design/approval gates |
| Campus staging / production | Not deployed | MIS infrastructure, acceptance and release approval |

### Also done — baseline observation kit (OF-4)

At your request I prepared the "before" baseline kit: [baseline-kit.pdf](baseline-kit.pdf), 7 A4 pages, editable source [baseline-kit.html](baseline-kit.html), with [CSV entry templates](baseline-templates/README.md). It holds the privacy rules, a permission form for each office head, the observation protocol and four sheets: arrivals tally, timed sample, records extract and day sheet. **Nobody has observed anything yet, and nothing has been sent to an office.** The two timed hours a day, the codes and the five-day sample are proposals for you and the adviser to change. Documentation only; committed locally on `docs/baseline-kit-2026-10-18`.

### Latest slice — combined GitHub release

The owner requested committing all changes, pushing and merging to main. The working tree was already clean, with 20 local commits. They are published together through [PR #11](https://github.com/EnrieJohn31/BISU-Registrar/pull/11), preserving the full human-authored history. `main` requires a PR, strict/up-to-date Foundation checks, administrator enforcement and resolved conversations. Required protections stay intact, and both foundation and browser jobs must pass on the exact final PR head before merge. Full local foundation checks passed again (167 tests / 944 assertions), along with 16 attribution cases and outgoing history/path/credential checks. [Release evidence and remaining checks](../docs/RELEASE-2026-10-18.md).

### Previous slice — daily LAN startup and connection links

The owner asked how to know the current IP and links every time the app starts. Added automatic selection to `lan-preview.ps1`: it uses the single active private IPv4 address with an IPv4 gateway, excluding gateway-less virtual adapters. Multiple qualifying addresses stop before container changes and require explicit `-Address`. `start-lan.cmd` launches the helper by absolute path, preserves its exit code and keeps the window open with links or errors. `-Links` reads actual running Docker bindings and active adapter addresses without restarting or rebuilding. It does not present an inactive bound address as a working phone link.

Daily use: start Docker Desktop, double-click `start-lan.cmd` or run `./lan-preview.ps1`, then use the printed **Phone / same LAN** URL. Show links again with `./lan-preview.ps1 -Links`; restore normal development with `./lan-preview.ps1 -Stop`. The helper changes no router/DHCP/firewall settings or source `.env`, and does not reserve a fixed IP. If DHCP changes the address, restart the preview and refresh any address-specific firewall rule or signed email link. [Instructions](../docs/LAN-PHONE-PREVIEW.md).

Validation: the actual Windows launcher ran without `-Address`, selected Wi-Fi `192.168.1.8`, built assets and printed all three links. Gateway-less virtual adapters were excluded. The read-only `-Links` check left container IDs and `.env` SHA-256 unchanged; a failure-path check confirmed the launcher preserves a nonzero helper exit code. Corrected PowerShell array-property enumeration during validation. Final syntax and both login/28-asset HTTP checks passed; six services remain running with Vite paused. No app/Compose source or account change and no new full foundation/browser/physical-device claim.

### Previous slice — same-network phone preview (OPS-1 preparation)

The user asked to access the app from a phone on the same network. Added `lan-preview.ps1` and an optional `docker-compose.lan.yml`: only app port 8085 is added on the selected private IPv4 address, with localhost retained. Assets are compiled and Vite is paused, so phone requests do not depend on its own localhost or a second port. App/worker/scheduler use the LAN `APP_URL` with debug disabled. The source `.env`, account/role data, database bindings and Mailpit access are preserved. No application source changed.

Start/stop, a scoped Windows firewall command, email-link behavior and troubleshooting are in [phone preview instructions](../docs/LAN-PHONE-PREVIEW.md). The PC test used Wi-Fi address `192.168.1.8` (may change with DHCP), app URL http://192.168.1.8:8085. Windows classifies this Wi-Fi as Public; that category was not changed. This agent has no administrator token, so no firewall rule was installed. If the phone cannot connect, run the documented rule in administrator PowerShell; it allows only TCP 8085 on the chosen Wi-Fi address/adapter from the local subnet.

Validation: runner syntax and merged Compose passed; login and all 28 referenced compiled assets returned HTTP 200 through both LAN and localhost URLs, with no HMR references. Stopping restored all seven base services and loopback-only app binding. The source environment SHA-256 stayed identical. Current preview leaves six services running and Vite stopped. No full foundation/browser regression rerun was needed for this root-only configuration slice; earlier results below are historical.

**Physical phone evidence, 2026-10-18:** the owner answered “Login page opens” when asked to open http://192.168.1.8:8085 on the same Wi-Fi. This confirms basic phone reachability only. Successful authentication/navigation, device/OS/browser versions, second-laptop access and whether the owner added a firewall rule were not reported. No runtime or account changes were made for this documentation follow-up.

### Previous slice — DEV-5, Part 05 slice 1 design

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

1. **Continue phone testing:** the login page already opens at http://192.168.1.8:8085. Sign in and check dashboard/navigation; record phone model/OS/browser version and results, then test a second laptop. Finish with `./lan-preview.ps1 -Stop` before returning to ordinary development or foundation checks; remove the preview firewall rule if created. [Preview instructions](../docs/LAN-PHONE-PREVIEW.md).
2. **Start the baseline (OF-4):** agree the method with the adviser, tell the Data Protection Officer, get each office head to sign the permission form (page 2 of the kit), print the sheets and observe. Nothing is measured until the signature is on file.
3. **You review the [Part 05 design](05-services-requirements-and-fees.md#slice-1-design-catalog-core-dev-5-2026-10-18)** and approve, change or reject the seven proposals (including the new `services.view` permission). On approval I record it as decision C15, then build DEV-6 on a new local branch with `./check.ps1`, MySQL race probes and new browser cases. Nothing in Part 05 is built before then.
4. **Alternative or parallel:** DEV-7 (basic queue) needs its own short design and approval first. Thesis and office-preparation tasks in [97](97-work-while-waiting.md) need no code.
5. **Send the [requirements checklist](requirements.pdf)** (version 2, 18 October: 37 items grouped by office, plain wording and made-up example answers; start with its "six to get first"). The rest of the Citizen's Charter (steps, fees, processing times and the Cashier section) is item 1 and unlocks Part 05 slice 2.
6. Record incoming office answers; they do not automatically authorize a previously gated feature. No messages, appointments or office permissions were sent or obtained by this slice.

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

- Active LAN preview: app http://192.168.1.8:8085 and http://localhost:8085; six services running, **Vite paused**. Captured-email inbox stays at http://localhost:8026 on the PC. No firewall rule was installed by this agent. The owner confirmed the physical phone login page opens; sign-in/navigation and device/browser details remain pending. This is not campus production.
- IP/link discovery: double-click `start-lan.cmd` for automatic startup, or run `./lan-preview.ps1 -Links` to read current bindings without a rebuild. The address above is a session snapshot; use the printed link after future restarts/network changes. If several gateway addresses qualify, choose with `-Address <PC-LAN-IPv4>`.
- Resume live development with `./lan-preview.ps1 -Stop`, which restores loopback-only app access and Vite at http://localhost:5178. Do this before `setup.ps1`, `check.ps1` or base Compose `up`, which can reset preview containers/hot markers. Rebuild the preview after code changes. Never remove database volumes for this task.
- Keep real records, private documents, secrets, runtime data and backups out of Git. Preserve actual human commit authorship and the existing attribution/push guards. Local commits only; publishing needs an explicit request.
- Existing registration-disabled builds fail because `Register.vue` imports a route omitted by generation in that configuration. Record a separate technical fix before using that setting; this slice did not change registration policy.
- Dompdf needs print-specific CSS; flex/grid and oversized table rows need reconsideration. Future real templates, complex scripts, signing/PDF standards, printer behavior, private storage, approval/versioning and queued retry/idempotency require their own design/acceptance.
- Font-metric caching and browser reuse may improve future throughput, but this spike measures fresh instances. Profile real workload before choosing operational limits. Restart long-running worker/scheduler after PHP job/config changes and keep job timeout below reservation expiry.
- For Part 05, enforce the office in the policy (the shared Office supervisor role lists `services.manage` and `fees.manage` for both offices), give composite index names explicitly (MySQL's 64-character limit failed in Part 04), compare JSON in tests with `assertEquals`, and keep PHP and TypeScript permission lists in step.
