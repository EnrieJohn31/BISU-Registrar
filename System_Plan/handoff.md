# Handoff

[Back to index](index.md) | [Change log](92-planning-change-log.md) | [Decision register](90-decisions-and-open-questions.md)

Rewrite this file after each completed step; keep dated history in the change log.

## Current handoff — 2026-10-17

**Stage:** M1 development; campus staging and production are not deployed. **Working branch:** `fix/part-03-browser-review`, based on the local accounts and Docker performance branches.

### Just completed

- Part 03 design published: PR #6 merged after required Foundation checks passed; merge `2cd523e`. Accounts implementation publication follows the final browser review.

- Supplier terminology is published: [PR #5](https://github.com/EnrieJohn31/BISU-Registrar/pull/5) merged after required Foundation checks passed; merge `22d284d`. Module scope is unchanged (C08).
- Part 03 browser workflow checks passed using disposable development data: staff creation/editing, assignment/revocation, first password change, staff 403, deactivation ending a session and reactivation restoring sign-in. Browser review is complete: 19 workflow/layout checks passed, including TOTP reset and password reissue. Audit keyboard activation and mobile expiry wrapping are fixed; starter sidebar developer links removed. See [review evidence](../docs/PART-03-BROWSER-REVIEW.md). This is not office UAT.

### Current implementation and validation

- [Part 03 slice 1](03-accounts-permissions-and-audit.md#slice-1-implementation-record): local staff accounts, office-scoped roles, TOTP requirements, temporary passwords, deactivation/session rules and append-only audit viewer. Sensitive roles require confirmed TOTP, because a passkey does not secure password sign-in (C09).
- Full checks passed again after review fixes: 97 tests / 498 assertions, Larastan level 7, PHP/frontend formatting, lint, Vue TypeScript and build. Development MySQL migration apply/rollback/reapply and HTTP smoke checks passed. No application check failures remain.
- Local dependency volumes and four PHP workers improve development response time; installing dependencies must happen in containers. Run `./setup.ps1` once on pulling the Docker volume change.
- Test credentials remain only in ignored `src/Credentials.md`; seeding resets these accounts. Browser QA creates synthetic accounts and disables them after use. Never publish credentials or real records.

### Next steps, in order

1. Publish the reviewed accounts branch, then Docker performance and browser/runtime fixes; require passing protected-main CI for each PR.
2. Publish the Part 03 design, accounts implementation and Docker performance branches in dependency order; require passing CI and normal protected-main PR merges. The project owner authorized proceeding in this session.
3. Prepare **Part 04 client/import design**: the owner reports no office documents yet. Document proposed fields, permissions, import/review/retry rules and phased acceptance checks; do not treat drafts as office policy or implement live client linking.
4. Bring the catalogue and [office checklist](95-office-interview-checklist.md) to Registrar/Cashier/MIS. Record named approval and evidence before real-data rollout.

### Waiting for office decisions

| Decision | Needed from | Current position |
|---|---|---|
| D03 | Registrar + MIS | Confirm identity proof, account recovery, role holders, configurable 30-minute idle / 72-hour temporary-password defaults and TOTP roles |
| D04 | Registrar records custodian | Approved columns/ID rules, source owner, duplicates and correction authority; no source file supplied |
| D05 | Registrar + Cashier | Citizen's Charter, approved services, fees and waivers; no amounts invented |
| D09 | DPO + MIS/office owners | Privacy notice, permitted fields, retention, audit access and real-data approval |
| D01, D20 | Sponsor/offices; MIS | First-release scope and supported device/browser baseline |

### Watch out

- Add permissions in PHP and TypeScript together; update matrix tests and the Part 03 catalogue when roles change.
- Use `AuditRecorder` in the same database transaction as each change. Add `AuditDeniedAccess` to future protected route groups.
- Dev Compose now opts into one-second Vite source polling so Windows bind-mount edits invalidate cached modules. Keep dependencies on Docker volumes and preserve watch exclusions.
- `src/nul` is an unrelated untracked Windows reserved-name artifact; keep it out of commits.
- If PowerShell blocks local scripts, use process-scoped `powershell.exe -NoProfile -ExecutionPolicy Bypass -File ./check.ps1`.
- Local `http://localhost:8085` verification is not campus deployment. Infrastructure and office acceptance remain open.
