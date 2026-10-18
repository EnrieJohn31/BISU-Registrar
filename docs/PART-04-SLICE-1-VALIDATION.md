# Part 04 slice 1 validation — 2026-10-17

[Part 04](../System_Plan/04-client-records-and-imports.md#slice-1-implementation-record) | [Current handoff](../System_Plan/handoff.md)

**Result:** passed. This is developer QA on local Docker with **synthetic records only**. It is not office UAT, not records-custodian acceptance and not a campus deployment.

## Automated checks

`powershell -NoProfile -ExecutionPolicy Bypass -File ./check.ps1` passed on the final code: Composer validation, production build and route generation, frontend formatting and lint, Vue TypeScript, PHP formatting (Pint), Larastan level 7, and **161 tests / 844 assertions** (97 before this slice; 64 new). The new tests cover:

- **Access by role (16 cases):** all 11 staff roles against list, detail, create, correct, verify, flag and ID actions. Only the Registrar processor may change records; Intake staff, approver and release officer may view; Cashier, Cashier supervisor, Registrar supervisor, report exporter, supplier roles and system administrator are refused. Guests, client accounts and deactivated staff are refused, and refusals are audited.
- **Records (34 cases):** create, leading zeroes, case-insensitive duplicates, same name kept as separate people, same text as Student and Alumni ID, invalid input, Unicode names, masked lists, search and filters, correction, no-op correction refused, stale edits on every action, verification and return to review, flagging, adding and retiring IDs, retired IDs never reusable, last ID protected, one revision and one audit event per change, audit holds no names or full IDs, revisions and profiles cannot be altered or deleted.
- **ID rule (28 cases):** the demo rule's valid and invalid values and masking.

## Real MySQL

SQLite cannot prove locking or collation behavior, so these ran against MySQL 8.4:

| Check | Result |
|---|---|
| Migrations apply, roll back and re-apply | Passed. This found a too-long index name that SQLite accepted; fixed with an explicit name |
| ID column collation and unique key | `utf8mb4_bin` and a unique key on type + canonical value, covering retired IDs |
| Full suite on a disposable MySQL database | 161 tests / 844 assertions passed. First run found 4 tests (2 from Part 03) that assumed JSON key order; MySQL re-orders JSON keys, so they now compare without regard to order. Application behavior was not affected |
| 8 parallel processes create the same ID at once | 1 created, 7 refused, no errors, no leftover profile from the losers |
| 8 parallel processes save from the same stale version | 1 saved, 7 refused as stale |
| 8 parallel processes add one new ID to two profiles | 1 added, 7 refused |
| 6 parallel processes retire the two IDs of one profile | 1 retired, 5 refused; never both |
| Consistency report afterwards | 0 duplicate ID keys; every profile's version equals its last revision number; no profile without a current ID |

The probe files and the disposable database were removed afterwards.

## Browser walkthrough

Isolated headless Microsoft Edge driven through the DevTools protocol against `http://localhost:8085`, desktop 1440 × 900 and phone 390 × 844. **34 checks passed, with no uncaught page exceptions and no unexpected console errors.**

1. Signed in as the Registrar processor: dashboard card, list with masked IDs, record count, search by name (3 matches for "reyes"), by ID start and by a leading-zero ID.
2. Created a client; the detail page shows the full ID and the creation in the history.
3. A duplicate ID was refused with a clear message and the form kept its values.
4. Corrected details (old → new shown in history), verified, corrected again (dialog warns, profile returned to "Needs review"), added an Alumni ID to the same person, retired an ID (kept as history).
5. Retiring the last current ID was refused with an explanation.
6. **Stale edit:** another edit was saved while the correction dialog was open. The save was refused, the latest data appeared, and a message explained why.
7. Phone layout: no page-level horizontal scroll on the list or detail page.
8. A very long name stays inside the table.
9. Signed in as Intake staff: sees the list and details, the "View only" notice, no change buttons, and a 403 on the create page.
10. The Cashier account needs two-factor at sign-in, so its denial is covered by the automated tests instead.

One finding fixed during review: the "ID type" and "ID" labels on the create form were misaligned; the grid now keeps them level. A first draft of the walkthrough also flagged a "Retire" false positive from text on the page; the check now looks at buttons.

## Not covered

Other browsers and physical kiosks or printers (D20), the light theme in this run, screen readers, office or custodian acceptance, real client data, the official ID format (D04), and retention and privacy review (D09). The walkthrough scripts and screenshots are kept in the session workspace, outside the repository; they contain only synthetic data.
