# Handoff

[Back to index](index.md) | [Waiting-work plan](97-work-while-waiting.md) | [Decision register](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

Rewrite this file after each completed slice; dated history belongs in the change log.

## Current handoff — 2026-10-18

**Stage:** M1 development. Part 03 slice 1 is merged. Part 04 slice 1, DEV-1 QA fixes (`896e59e`), DEV-2 background services (`b614386`) and DEV-3 browser regression remain local. Current branch: `test/browser-regression-2026-10-18`, based on `feat/dev-background-services-2026-10-18`. Campus staging/production are not deployed.

**Current scope (C11/C12): work only on items that can proceed while offices answer.** The owner authorized DEV-3 as the next eligible item. Preserve later slices' design/approval gates; an incoming office answer does not automatically authorize a gated business feature. Keep synthetic data labelled. Imports, linking, office workflows and real fees/IDs/templates remain outside current work.

**Git policy:** local commits only; no push, PR or GitHub merge without an explicit owner request. Preserve actual human authorship and the active commit/push guards. Historical attribution details: [record](../docs/CONTRIBUTOR-ATTRIBUTION-CLEANUP.md).

### Just completed — DEV-3

- Saved 34 named client-record browser cases with Playwright, each run at desktop/phone widths in light/dark mode: 136 checks, zero retries. Coverage includes real authentication/TOTP, search/filtering, writes/conflicts, stale screens, permissions, audit privacy, all 51 history revisions and keyboard/layout checks.
- Added `./browser-tests.ps1` and a separate fixed `bisu-registrar-browser` Compose project. It copies allowlisted source read-only into a disposable workspace, installs locked dependencies, builds assets and seeds SQLite. Development environment files, credentials, runtime data, dependency volumes and network/database are excluded. It exposes no host app port.
- Generated keys, credentials, TOTP secrets and sessions disappear with the workspace. Seeder guards require testing mode, explicit opt-in, SQLite and the exact isolated path; a nonempty database is refused. Four guard tests run in the foundation suite.
- Saved reports, failure traces and layout captures in ignored `.browser-artifacts/`. Browser TypeScript joins `check.ps1`; a separate CI browser job uses the same runner and seven-day synthetic artifact retention. No GitHub execution/upload is claimed because this branch is unpublished.
- Part 04's pending developer visual follow-up is complete: history page 2 reaches creation and returns to the newest revisions; desktop/phone controls and wrapping were reviewed. No application UI/controller/route, permission, migration or office rule changed in DEV-3.

Design, complete case map, commands, findings and evidence: [DEV-3 report](../docs/DEV-3-BROWSER-REGRESSION.md).

### Validation and runtime

- Browser matrix: **136 checks passed**, covering Chromium at 1440 × 900 and phone emulation at 390 × 844, both themes. Final strengthened B21/B27/B33 assertions passed another 12/12 focused checks. Both reports are retained locally as documented in the DEV-3 record. Real Edge/Safari/Firefox installations and physical campus devices remain D20 acceptance work.
- `./check.ps1`: **167 tests / 944 assertions passed**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan level 7. Vite/worker/scheduler were restored afterwards.
- Browser isolation checks matched the development `.env` SHA-256 and user/profile/revision/audit counts plus profile-version sum before/after full runs. Only the separate test project's workspace/network/containers are removed; no development records were reset or edited by this suite.
- Seven development services remain in place: app, Vite, database worker, scheduler, Mailpit, MySQL and Redis. DEV-2's live retry/email/failure probes are historical evidence in [its report](../docs/DEV-2-BACKGROUND-SERVICES.md), not rerun by DEV-3.
- URLs: app http://localhost:8085; Vite http://localhost:5178; captured test inbox http://localhost:8026. This is development Docker, not campus deployment.

### Part 04 state

Client records retain C10's approved permissions and demo rules. Full history is paginated; free-text client reasons stay in restricted revisions and general audit events reference the revision. Existing demo accounts/clients were preserved by DEV-3.

[DEV-1 QA](../docs/PART-04-QA-2026-10-18.md): disposable MySQL 45 tests / 394 assertions. The original Edge/race evidence remains dated 2026-10-17. DEV-3 adds repeatable Chromium evidence, not new MySQL race probes or office acceptance.

Development access remains `processor@bisu.test` (changes) and `intake@bisu.test` (view only); credentials stay in ignored `src/Credentials.md`. Browser-test accounts have fresh random credentials and separate disposable storage. Never reset development fixtures merely to repeat browser QA.

### Decisions and blockers

| Decision | Owner | Still needed |
|---|---|---|
| D03 / D04 / D09 | Registrar + MIS + DPO | Linking proof/recovery, official IDs, approved import source/reviewer/correction authority, permitted fields, privacy/retention/access auditing |
| D05 / D07 | Registrar + Cashier | Complete charter, approved services/requirements/fees and financial rules |
| D08 | Registrar | Real templates, signatures, numbering and release rules |
| D11 / D13 / D20 | MIS + offices | Campus mail/assisted delivery, workload and operational targets, supported browsers/devices |

The browser suite resolves a technical regression prerequisite. It does not approve any of these office decisions.

### Next work, within the waiting-work list

1. DEV-8: use a supplied/verified official BISU logo for sidebar/browser icons and clean up the pre-existing stray `src/nul`. Inspect available assets first; do not invent an official mark. These files remain untouched by DEV-3.
2. DEV-4: compare two PDF renderers using only a made-up certificate stamped “SAMPLE – NOT VALID”; then DEV-5: write the Part 05 design from the charter field layout. Part 05 implementation still requires approval of that design.
3. Team/office preparation remains in [97](97-work-while-waiting.md): names/dates, thesis drafts/diagrams, demo/device checks, privacy draft and permission-based anonymous timing baseline. No messages or bookings were sent by DEV-3.
4. Record office answers in the decision register and preserve C11. Publishing local branches requires a separate explicit request.

### Watch-outs and useful optimizations

- The suite uses the existing default registration-enabled build. A registration-disabled build currently fails because `Register.vue` imports a route omitted by generation in that configuration. Record a separate technical fix/build check before using that setting; DEV-3 does not change registration policy.
- Keep Playwright package/image versions aligned. Run one local browser runner at a time because its project name is fixed. Consider test-only download caches if repeated fresh installs become slow; do not share writable dev runtime/dependency volumes.
- Restart worker/scheduler after PHP job/config changes. Keep timeout below reservation expiry; future jobs need measured retry/idempotency settings. Do not invent cleanup schedules before retention approval.
- Preserve generated routes and server-side permissions. Every client write goes through `ClientProfileRecorder`; full details/reasons remain outside general audit.
- Keep secrets, real records, runtime data and backups out of Git. `.browser-artifacts/` and the unrelated `src/nul` are excluded from this commit.
