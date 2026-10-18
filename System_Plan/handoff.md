# Handoff

[Back to index](index.md) | [Part 04](04-client-records-and-imports.md) | [Change log](92-planning-change-log.md) | [Decision register](90-decisions-and-open-questions.md)

Rewrite this file after each completed slice; dated history belongs in the change log.

## Current handoff — 2026-10-18

**Stage:** M1 development. Part 03 slice 1 is merged. Part 04 slice 1 and its follow-up QA fixes are local on `fix/part-04-qa-2026-10-18`, based on `docs/while-waiting-plan-2026-10-18`. Campus staging and production are not deployed.

**Git policy:** commit locally only. Do not push, open a PR or merge on GitHub without the owner's explicit request. Preserve actual human authorship; local hooks and required CI enforce the attribution rule. Historical cleanup/configuration details are in the [attribution record](../docs/CONTRIBUTOR-ATTRIBUTION-CLEANUP.md) and change log.

### Just completed — Part 04 follow-up QA (DEV-1)

- Fixed inaccessible history beyond the latest 50 changes. Detail history now uses the shared pagination component, with 50 revisions per page, newest first; Intake may read older history without change access.
- Fixed free-text client reasons leaking into the general audit viewer. All six client actions keep the original reason in restricted revision history; general audit records point to that revision and retain action/actor/office and masked metadata. Existing audit rows are not rewritten.
- Added regressions for 51 revisions, separate-profile isolation, the actual next-page link, role restrictions on history pages, every action's reason and both administrator/supervisor audit views. Both new tests failed before the fixes; an independent review found no regression in the final diff.
- No migrations, new permissions, office-rule changes, imports or linking. Existing demo records were not reset or edited. Details: [QA report](../docs/PART-04-QA-2026-10-18.md).

### Validation and local runtime

- Final `./check.ps1`: **163 tests / 936 assertions passed**, plus Composer validation, production build/Wayfinder generation, frontend formatting/lint, Vue TypeScript, Pint and Larastan level 7. Baseline before the fixes: 161 tests / 844 assertions.
- Part 04 on disposable MySQL 8.4: **45 tests / 394 assertions passed**; the test database was removed afterwards. Tests use an in-memory SQLite database or that separately created MySQL database, never the development database. Details are in the QA report.
- Docker Desktop and the existing app/Vite/MySQL/Redis containers were started for QA. App URL: http://localhost:8085. The development Compose still has no worker, scheduler or test inbox.
- **Browser confirmation pending:** the computer-use tool exposed no enabled apps/browsers. The 34-check Edge walkthrough and race probes dated 2026-10-17 remain historical evidence in the [original validation](../docs/PART-04-SLICE-1-VALIDATION.md); neither was rerun here. Visually check history paging on desktop and phone when a browser is available. Office/custodian acceptance remains pending.

### Demo and access reference

Sign in as `processor@bisu.test` (changes allowed) or `intake@bisu.test` (view only); sign-in details remain in ignored `src/Credentials.md`. The README documents development seeders if fixtures are missing; do not reset existing data merely to repeat QA.

C10 still controls the slice: minimal fields; labelled demo ID normalization; IDs never reused, even retired; same names never merged; reasons and stale versions required; name/category corrections return verified profiles to needs-review; the last current ID cannot be retired. Intake, Registrar processor/approver and release officer may view; only the Registrar processor may change. Supervisors and system administrators receive no general client access.

### Decisions needed

| Decision | Owner | Needed answer |
|---|---|---|
| D04 | Registrar records custodian | Official ID formats, source/sample columns, verification and correction authority, duplicate handling |
| D03 / D09 | Registrar + MIS + DPO | Linking proof/recovery; permitted fields, privacy notice, retention, correction/erasure and access auditing |
| D05 / D07 | Registrar + Cashier | Complete Citizen's Charter, approved service/requirement/fee entries and financial rules |
| D01 / D13 / D20 | Sponsor/offices + MIS | Release scope, workload/worker resources and supported browser/device baseline |

Do not infer policy changes from QA. Supervisor client access, access auditing and independent verification still need explicit decisions. Only charter page 115 has been received; it establishes an entry layout, not approved fees or services.

### Next steps, in order

1. Confirm the new history controls visually on desktop and phone when browser access is available; record the result in the QA report. The full-history regression constructs 51 revisions in an isolated test database.
2. DEV-2: prepare the scoped design for a development queue worker, scheduler and Mailpit, then obtain the build approval required by [97](97-work-while-waiting.md). No change to the campus mail relay or production deployment is authorized by this QA task.
3. Follow the waiting-work plan for saved browser tests, supplied BISU branding, the synthetic PDF comparison and Part 05 design. Part 05 implementation waits for owner approval of that design.
4. Team/office work remains in [97](97-work-while-waiting.md): assign names/dates, chapters/diagrams, office demo and device checks, privacy draft and permission-based anonymous timing baseline. No messages or bookings were sent by this QA task.
5. Record office answers in the decision register. Imports (slice 2) and account linking (slice 3) retain their separate gates. Publishing any local branch requires an explicit owner request.

### Blockers and watch-outs

- Real data and the official ID rule wait for D04/D09; linking also waits for D03. Import work needs its approved source/reviewer/retention rules and a worker.
- Keep every client write through `ClientProfileRecorder`; free-text reasons belong only in restricted history. New code must never pass client names/full IDs into general audit fields.
- `revisions` is now `Paginated<ClientRevision>`; use `revisions.data`. Preserve the generated route and existing auth/permission connections.
- MySQL reorders JSON keys; compare audit/revision objects without assuming key order. Keep PHP/TypeScript permission catalogues synchronized.
- Keep secrets, private records, runtime data and local backups out of Git. The pre-existing untracked `src/nul` is unrelated and remains excluded; its cleanup belongs to DEV-8.
