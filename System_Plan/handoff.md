# Handoff

[Back to index](index.md) | [Change log](92-planning-change-log.md) | [Decision register](90-decisions-and-open-questions.md)

This file is **rewritten, not appended**, after every completed plan part or implementation slice. It always describes where the project stands now and what the next person should do. Dated history stays in the [change log](92-planning-change-log.md).

## Current handoff — 2026-10-17

**Stage:** M1 development. **Just finished:** Part 03 slice 1 (staff accounts, roles, audit trail). **Working branch:** `feat/part-03-accounts-roles-audit`, local only, committed, **not pushed**. It sits on top of `docs/part-03-accounts-design-2026-10-17` and the open supplier-rename branch.

### Just completed: Part 03 slice 1

Details, file list and design changes are in the [Part 03 implementation record](03-accounts-permissions-and-audit.md#slice-1-implementation-record). What now works:

- **Staff accounts:** administrators create staff accounts with a temporary password handed over in person. People must set their own password at first sign-in, and set up two-factor if their role needs it.
- **Roles:** 10 roles, each tied to an office, assigned and revoked with a reason. Full history is kept, nobody can change their own roles, and at least one active administrator always remains.
- **Deactivation:** refuses password, two-factor and passkey sign-in, ends sessions, and keeps the account's history.
- **Staff session rules:** staff are signed out after 30 idle minutes and cannot be restored from a remember-me cookie.
- **Audit trail:** append-only, with no secrets stored, and a viewer scoped by office.
- **Starter fixes:** self-registered accounts get no roles, self-service deletion is gone, and the data shared with every page is an explicit allow-list.
- **Bootstrap and test data:** `bisu:create-admin` creates the first administrator on a server; `DevelopmentAccountsSeeder` creates local test accounts.

**Design change you should know about:** the approved design said "two-factor or a passkey". The code requires confirmed TOTP two-factor for sensitive roles, because a passkey does not protect password sign-in. Recorded in C09.

### Validation done

- `./check.ps1` passed: 97 tests / 498 assertions (57 new), Larastan level 7, lint, formatting, `vue-tsc`, build.
- The four migrations were applied, rolled back and re-applied on development MySQL.
- End-to-end smoke test on the running stack passed: sign-in, two-factor challenge, admin pages, 403 for other staff, temporary-password redirect, deactivated sign-in refused, audit rows written.
- **Not done:** a visual review of the new screens in a browser.

### Test accounts

Run the seeder (see README, "Daily commands"). Sign-in details are written to `src/Credentials.md`, which is git-ignored:
- 15 accounts: one per role, a client, a first-sign-in account and a deactivated account
- password `password`, except the first-sign-in account, which has a temporary password
- two-factor secrets and recovery codes for the sensitive roles

The file explains how to get a current two-factor code.

### Decisions needed

| # | Question | Recommendation | Who decides | Answer |
|---|---|---|---|---|
| 1 | Do the role names and holders match office practice? | Show the Part 03 catalogue at the Registrar and Cashier meetings | Registrar, Cashier (D03, D07, D08) | |
| 2 | Confirm 30-minute staff idle timeout, 72-hour temporary passwords, two-factor roles | Keep the current values; all three are configurable in `.env` | Offices + MIS (D03) | |
| 3 | Push and open a PR for review? | Yes, after the visual check, and after merging the supplier-rename PR | Project owner | |

### Next steps, in order

1. **Visual check:** sign in with the test accounts and walk through Users, Create staff, user detail (assign/revoke role, deactivate, temporary password, reset two-factor), the audit trail, the first-sign-in password change and the dashboard. Note anything that looks wrong.
2. **Merge order:** supplier-rename PR, then `docs/part-03-accounts-design-2026-10-17`, then this branch. Push only when the project owner says so.
3. **Office meetings** ([95](95-office-interview-checklist.md)): bring the role catalogue; continue with R1–R5 for Parts 04–05.
4. **Next slice:** Part 05 (services and fees) if the Citizen's Charter is in hand, otherwise Part 04 (client records and imports). Write its design first, like Part 03.

### Blocked or waiting

- Still-open M1 office decisions: D01, D04, D05, D09, D20. D03 is partly settled by C09, but its values need office/MIS confirmation.
- Nothing is pushed: the branches exist only on this computer. Back them up or push them when ready.

### Watch out

- New permissions go in `app/Enums/Permission.php` **and** `resources/js/types/permissions.ts`; a test fails if they differ. Role changes must update `tests/Unit/RolePermissionMatrixTest.php` and the Part 03 catalogue together.
- Write every audit event through `AuditRecorder`, inside the same transaction as the change.
- Admin route groups for later modules (for example financial actions) should also use the `AuditDeniedAccess` middleware.
- The sidebar still shows the Laravel starter's "Repository" and "Documentation" links; remove or replace them in a UI tidy-up.
- On Windows, run `powershell -NoProfile -ExecutionPolicy Bypass -File ./check.ps1` if scripts are blocked.

## Template for the next handoff

```text
## Current handoff — YYYY-MM-DD

Stage / milestone / working branch:
Just completed (with links):
Validation done:
Decisions needed (question | recommendation | who decides | answer):
Next steps, in order:
Blocked or waiting:
Watch out:
```
