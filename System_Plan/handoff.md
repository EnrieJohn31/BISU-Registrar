# Handoff

[Back to index](index.md) | [Waiting-work plan](97-work-while-waiting.md) | [Decision register](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

Rewrite this file after each completed slice; dated history belongs in the change log.

## Current handoff — 2026-10-18

**Stage:** M1 development. Part 03 slice 1 is merged. Part 04 slice 1, DEV-1 QA fixes (`896e59e`), DEV-2 background services (`b614386`), DEV-3 browser regression (`0dc56c6`) and DEV-8 branding remain local. Current branch: `feat/bisu-branding-2026-10-18`, based on `test/browser-regression-2026-10-18`. Campus staging/production are not deployed.

**Current scope (C11–C13): work only on items that can proceed while offices answer.** The owner authorized DEV-8 as the next eligible item. Preserve later slices' design/approval gates; an incoming office answer does not automatically authorize a gated business feature. Keep synthetic data labelled. Imports, linking, office workflows and real fees/IDs/templates remain outside current work.

**Git policy:** local commits only; no push, PR or GitHub merge without an explicit owner request. Preserve actual human authorship and the active commit/push guards. Historical attribution details: [record](../docs/CONTRIBUTOR-ATTRIBUTION-CLEANUP.md).

### Just completed — DEV-8

- Replaced the starter shared/sidebar mark with the existing BISU seal from the official campus media library, verified against the campus header. The unchanged original and provenance live in `src/resources/branding/`; a 96-pixel local derivative serves the shared component. Expanded/collapsed desktop and phone drawer captures were reviewed in both themes.
- Replaced browser icons with local 64-pixel PNG, 16/32/48-pixel ICO and 180-pixel touch assets. The Blade shell uses new cache keys; the starter SVG is removed. Public/login/staff pages receive the same local references. No external request is needed to load branding.
- Added a Windows maintenance generator, `scripts/build-brand-icons.ps1`. Repeated generation produces identical hashes; no package or app dependency was added. The shared prop/attribute contract, app name, generated dashboard link and authentication remain connected.
- Removed only the verified untracked `src/nul` diagnostic file and ignored its exact path. No database, credential, runtime record, office rule, controller, route or permission changed.

Source, design, commands and current validation: [DEV-8 report](../docs/DEV-8-BISU-BRANDING.md). Saved browser suite/case map: [DEV-3 report](../docs/DEV-3-BROWSER-REGRESSION.md).

### Validation and runtime

- Branding inspection passed: 32-pixel seal fits its generated dashboard link in expanded/collapsed desktop navigation and phone drawers, in both themes. Six captures and an inspection JSON stay in ignored `.browser-artifacts/branding/`. Browser icon responses match the committed files byte for byte; public/login shell references are also verified.
- The saved browser matrix rerun passed **136 checks**, covering Chromium at 1440 × 900 and phone emulation at 390 × 844, both themes. Real Edge/Safari/Firefox installations and physical campus devices remain D20 acceptance work.
- `./check.ps1`: **167 tests / 944 assertions passed**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan level 7. Vite/worker/scheduler were restored afterwards.
- Browser isolation checks matched the development `.env` SHA-256 and user/profile/revision/audit counts plus profile-version sum before/after full runs. Only the separate test project's workspace/network/containers are removed; no development records were reset or edited by this suite.
- Seven development services remain in place: app, Vite, database worker, scheduler, Mailpit, MySQL and Redis. DEV-2's live retry/email/failure probes are historical evidence in [its report](../docs/DEV-2-BACKGROUND-SERVICES.md), not rerun by branding QA.
- URLs: app http://localhost:8085; Vite http://localhost:5178; captured test inbox http://localhost:8026. This is development Docker, not campus deployment.

### Part 04 state

Client records retain C10's approved permissions and demo rules. Full history is paginated; free-text client reasons stay in restricted revisions and general audit events reference the revision. The full-history developer visual follow-up was completed in DEV-3. Existing demo accounts/clients were preserved by branding QA.

[DEV-1 QA](../docs/PART-04-QA-2026-10-18.md): disposable MySQL 45 tests / 394 assertions. The original Edge/race evidence remains dated 2026-10-17. DEV-3 adds repeatable Chromium evidence, not new MySQL race probes or office acceptance.

Development access remains `processor@bisu.test` (changes) and `intake@bisu.test` (view only); credentials stay in ignored `src/Credentials.md`. Browser-test accounts have fresh random credentials and separate disposable storage. Never reset development fixtures merely to repeat browser QA.

### Decisions and blockers

| Decision | Owner | Still needed |
|---|---|---|
| D03 / D04 / D09 | Registrar + MIS + DPO | Linking proof/recovery, official IDs, approved import source/reviewer/correction authority, permitted fields, privacy/retention/access auditing |
| D05 / D07 | Registrar + Cashier | Complete charter, approved services/requirements/fees and financial rules |
| D08 | Registrar | Real templates, signatures, numbering and release rules |
| D11 / D13 / D20 | MIS + offices | Campus mail/assisted delivery, workload and operational targets, supported browsers/devices |

Development branding and browser regression do not approve any of these office decisions.

### Next work, within the waiting-work list

1. DEV-4: compare two PDF renderers using only a made-up certificate stamped “SAMPLE – NOT VALID”, with bundled fonts, A4/Legal, long names and multiple pages. No real wording, signature or numbering; D08 remains open.
2. DEV-5: write the Part 05 design from the charter field layout. Part 05 implementation still requires approval of that design. Later build slices keep their own scoped tasks/gates.
3. Team/office preparation remains in [97](97-work-while-waiting.md): names/dates, thesis drafts/diagrams, demo/device checks, privacy draft and permission-based anonymous timing baseline. No messages or bookings were sent by DEV-8.
4. Record office answers in the decision register and preserve C11. Publishing local branches requires a separate explicit request.

### Watch-outs and useful optimizations

- The suite uses the existing default registration-enabled build. A registration-disabled build currently fails because `Register.vue` imports a route omitted by generation in that configuration. Record a separate technical fix/build check before using that setting; DEV-3 does not change registration policy.
- Keep Playwright package/image versions aligned. Run one local browser runner at a time because its project name is fixed. Consider test-only download caches if repeated fresh installs become slow; do not share writable dev runtime/dependency volumes.
- Restart worker/scheduler after PHP job/config changes. Keep timeout below reservation expiry; future jobs need measured retry/idempotency settings. Do not invent cleanup schedules before retention approval.
- Preserve generated routes and server-side permissions. Every client write goes through `ClientProfileRecorder`; full details/reasons remain outside general audit.
- Keep the full seal source outside public assets; use the 96-pixel derivative across layouts. Regenerate on Windows from the preserved source and update browser cache keys when replacing assets. A dedicated small official icon can replace the seal later if MIS supplies one.
- Keep secrets, real records, runtime data and backups out of Git. `.browser-artifacts/` stays ignored; the stray `src/nul` is now removed and explicitly ignored.
