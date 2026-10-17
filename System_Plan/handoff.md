# Handoff

[Back to index](index.md) | [Part 03](03-accounts-permissions-and-audit.md) | [Part 04 design](04-client-records-and-imports.md) | [Change log](92-planning-change-log.md)

Rewrite this file after each completed slice; dated history belongs in the change log.

## Current handoff — 2026-10-17

**Stage:** M1 development. Part 03 slice 1 is implemented, reviewed and merged. Part 04 is a detailed design draft; no client/import/linking code has been added. **Campus staging and production:** not deployed.

### Just completed

- **Staff accounts, office roles and audit:** administrators provision accounts and temporary passwords; first sign-in requires a password change; sensitive roles require confirmed TOTP. Role changes need reasons, preserve history, prohibit self-management and preserve the last active administrator. Deactivation blocks sign-in and ends sessions. Staff idle timeout is 30 minutes; remember-me does not restore staff sessions. Audit data is append-only and office-scoped. Full scope and C09 deviations are in the [implementation record](03-accounts-permissions-and-audit.md#slice-1-implementation-record).
- **Browser review and fixes:** 19 synthetic workflow/layout checks passed. Fixed audit details immediately closing on Enter, added Space activation, wrapped long mobile expiry text and removed starter sidebar developer links. TOTP reset and password reissue were exercised. See [coverage/evidence](../docs/PART-03-BROWSER-REVIEW.md); this is developer QA, not office UAT.
- **Docker development:** dependencies use Docker volumes and PHP uses four development workers. One-second Vite source polling fixes stale modules across Windows bind mounts. Source edits were verified in the served module; dependency/metadata exclusions remain in place.
- **Part 04 design:** [three delivery slices](04-client-records-and-imports.md#delivery-slices-and-gates): client records first, reviewed create-only imports second, identity linking later. Includes proposed fields/constraints, permissions, routes, private files, immutable review/replay/retry rules, concurrency and acceptance checks. Office rules remain explicitly unapproved; no real records or proof documents were used.

### Publication evidence

| Work | Protected-main publication |
|---|---|
| Supplier terminology (C08) | [PR #5](https://github.com/EnrieJohn31/BISU-Registrar/pull/5), merge `22d284d`; required CI passed |
| Part 03 design | [PR #6](https://github.com/EnrieJohn31/BISU-Registrar/pull/6), merge `2cd523e`; required CI passed |
| Part 03 accounts + browser fixes | [PR #7](https://github.com/EnrieJohn31/BISU-Registrar/pull/7), merge `fa2246a`; [required CI](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38999900147) passed |
| Docker dependency volumes/workers | [PR #8](https://github.com/EnrieJohn31/BISU-Registrar/pull/8), merge `4d69e06`; [required CI](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/39000107485) passed |
| Source polling, review evidence and Part 04 draft | Delivered by [PR #9](https://github.com/EnrieJohn31/BISU-Registrar/pull/9); use its timeline for merge/CI evidence |

No protected-branch rule was bypassed. The linked PR timeline records the exact final merge/CI status of the documentation containing this handoff.

### Validation

- `check.ps1` passed after code changes: **97 tests / 498 assertions**, Larastan level 7, PHP/frontend formatting, lint, Vue TypeScript and production asset build. Later edits are documentation only; local Markdown file/heading links were checked separately.
- Development MySQL migration apply/rollback/reapply and authenticated smoke checks passed; browser QA used Edge 155.0.4283.45, 1440 × 1000 desktop and 390 × 844 mobile viewports, both themes. Enter/Space retain the audit detail sheet; no uncaught page errors in the final workflow run. Physical kiosk/printing and other campus browsers remain untested (D20).
- Local app/Vite running; MySQL/Redis healthy. Test credentials stay in ignored `src/Credentials.md`. The two disposable browser-QA accounts were deactivated afterward; existing seeder accounts were preserved.

### Next steps, in order

1. **Review Part 04 slice 1** with the team/Registrar: agree minimal fields, ID comparison/history rules, custodian/correction authority and who verifies a profile. Use the concrete [decision questions](04-client-records-and-imports.md#decisions-to-bring-to-registrar--mis--dpo) and [office checklist](95-office-interview-checklist.md).
2. After the scoped slice 1 design is approved, implement additive client/identifier/revision tables, policies, staff search/detail/create/correction and audit using **synthetic fixtures**. Test identifier conflicts, stale edits, office permissions, MySQL constraints and browser flows. Update Part 04, this handoff, index and change log after completion.
3. Build imports only as slice 2 with approved template/source/reviewer rules, private staging, durable row outcomes and a worker/scheduler. Build account linking only as slice 3 after proof/recovery/notice rules are approved. The current Compose has no dedicated queue worker.
4. Continue Part 03 office acceptance and gather the Citizen's Charter/service/fee inputs for Part 05. Real-data rollout and campus deployment require separate office/MIS/privacy acceptance and Part 14 evidence.

### Decisions still needed

| Decision | Owner | Required answer |
|---|---|---|
| D03 | Registrar + MIS | Identity proof/recovery, role holders, confirmation of configurable 30-minute idle / 72-hour temporary-password defaults and TOTP roles |
| D04 | Registrar records custodian | Approved source/sample/columns, ID formats/normalization/reuse, duplicate/correction authority, importer/reviewer duties; no office documents supplied yet |
| D05 | Registrar + Cashier | Citizen's Charter, services, requirements, fees/waivers; no amounts invented |
| D09 | DPO + MIS/office owners | Permitted fields/evidence, notice, retention, correction/deletion, audit access and real-data approval |
| D01, D13, D20 | Sponsor/offices + MIS | Release scope, import workload/worker/recovery targets, supported campus devices/browsers |

### Watch out

- Current Office supervisor includes `clients.import` for Registrar and Cashier. **Part 04 must require Registrar office scope**; a Cashier-only supervisor must be denied. Registrar supervisors do not currently have general `clients.view/manage`. Independent import approval is a proposed rule, not an approved map change.
- Keep PHP/TypeScript permission catalogues, matrix tests and Part 03 documentation synchronized. Use `AuditRecorder` within the same transaction as each change, and `AuditDeniedAccess` on new protected route groups.
- Run `./setup.ps1` once after pulling the dependency-volume change. Install packages inside containers; host `src/vendor`/`src/node_modules` copies are no longer used at runtime. If scripts are blocked, use process-scoped `powershell.exe -NoProfile -ExecutionPolicy Bypass -File ./check.ps1`.
- Rerunning `DevelopmentAccountsSeeder` resets its test passwords/TOTP secrets; never run it on a campus server. Do not commit credentials or real records. Local `http://localhost:8085` is not a campus production URL.
- Unrelated untracked `src/nul` was preserved and excluded from all commits. Dependabot PRs #1–3 were outside this task and remain untouched.
