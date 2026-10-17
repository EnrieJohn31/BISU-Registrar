# Part 03 browser review — 2026-10-17

[Part 03](../System_Plan/03-accounts-permissions-and-audit.md) | [Current handoff](../System_Plan/handoff.md)

**Result:** passed after the fixes below. This is developer QA on local Docker, not office UAT or campus deployment.

## Environment and coverage

- Isolated headless Microsoft Edge; desktop 1440 × 1000 and mobile viewport 390 × 844, light and dark themes. Windows control runtime was unavailable; this does not test physical kiosks, printers, Safari/Firefox or the campus browser baseline (D20).
- Local `http://localhost:8085`; synthetic seeder accounts and disposable staff fixtures only. Disposable accounts were deactivated after review. No existing staff credentials were reset.
- Walkthrough: dashboard, Users/search/empty state, staff form, temporary-password screen, account details/edit dialog, role assignment/revocation, deactivation/reactivation, audit list and keyboard-opened detail sheet, first-sign-in password form.
- Functional checks: administrator password/TOTP sign-in; create/edit/assign/revoke; required first password change; ordinary staff denied Users (403); confirmed TOTP setup followed by administrator reset; deactivation invalidates an existing session; reactivation permits sign-in; password reissue ends the old session and requires another password change.
- Final run: 19 recorded workflow/layout checks passed, no uncaught page errors. No document-wide horizontal overflow at the tested widths; tables scroll within their containers. Screenshots inspected for legibility/dialog layout. Generated passwords were masked before capture; TOTP setup/recovery data was never captured.

## Findings and fixes

| Finding | Change | Verification |
|---|---|---|
| Pressing Enter on an audit row opened then immediately closed the details sheet | Prevent Enter's default activation; support Space with prevented default; wrap long detail text | Enter now leaves the populated sheet open on desktop and mobile; close still works |
| Long first-sign-in expiry badge clipped on mobile | Wrap status text within available width; allow long user heading text to wrap | 390 px screenshot shows complete expiry text |
| Staff sidebar linked to the Laravel starter repository/docs | Remove developer footer links; preserve permission-filtered navigation and account menu | Staff navigation reviewed in both themes |
| Windows Docker bind-mount edits were not invalidating Vite's module cache | Opt-in Vite source polling from dev Compose (`VITE_USE_POLLING=true`, 1000 ms); existing dependency/metadata exclusions retained | Served module initially lacked the fix; after enabling polling, host formatting edits automatically appeared in served source |

## Required checks

`powershell.exe -NoProfile -ExecutionPolicy Bypass -File ./check.ps1` passed after the changes: Composer validation, production asset build, frontend formatting/lint, Vue TypeScript, PHP formatting, Larastan level 7 and **97 tests / 498 assertions**. Node was restarted by the check script and is serving the local frontend. Earlier development MySQL migration apply/rollback/reapply evidence remains in Part 03.

The local automation, redacted screenshots and machine-readable results are retained in the session workspace (`browser-review/`), outside the application repository. Do not publish test credentials or actual client data. Remaining gates: office role/policy acceptance, D20 devices/browsers, D09 privacy review and Part 14 deployment evidence.
