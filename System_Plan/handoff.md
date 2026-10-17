# Handoff

[Back to index](index.md) | [Part 04](04-client-records-and-imports.md) | [Change log](92-planning-change-log.md) | [Decision register](90-decisions-and-open-questions.md)

Rewrite this file after each completed slice; dated history belongs in the change log.

## Current handoff — 2026-10-17

**Git policy (project owner, 2026-10-17): local commits only.** Work on a local branch and commit there. Do **not** push, open pull requests or merge on GitHub unless the owner explicitly asks. Earlier work (PRs #5–#9) was published before this rule; the owner separately approved the credit-history cleanup and prevention PR #10. Part 04 implementation and the current local-only records remain unpushed.

**Stage:** M1 development. Part 03 slice 1 (staff accounts, roles, audit) is merged. **Part 04 slice 1 (client records) is built and committed on the local branch `feat/part-04-client-records`, ready for you to test.** Imports (slice 2) and account linking (slice 3) are design only. Campus staging and production: not deployed.

### Commit attribution cleanup - 2026-10-17

- **Completed with owner approval:** three published Claude trailers removed by an atomic exact-lease history rewrite; clean main `2e83568`. Every application tree and human identity/time preserved. All original main protection settings restored and verified. Four old signatures were removed from changed commit objects, as approved; recovery bundles/maps remain local.
- Cleaned the two unpublished credits and aligned local ancestry: policy `878964d` -> `8402d30` -> `069e6cb`; Part 04 implementation `cca3936` -> `d577d76` -> `0ae71f6`. Current client code and local test data are unchanged; Part 04 stays unpushed.
- **Human authors only:** the owner explicitly requires actual people as authors/co-authors. AGENTS.md records that rule; tested commit/push guards and required-CI attribution checks are delivered through [PR #10](https://github.com/EnrieJohn31/BISU-Registrar/pull/10). Activate hooks in existing clones with `./install-git-hooks.ps1`; new setup runs it automatically. PR #10 merged as `c331ae3` after all required checks passed. Local commit/push hooks are active via the installer; setup installs them in new checkouts.
- Verified 24 GitHub commits: zero Claude credits, human account author names only; clean-main CI passed. GitHub contributor caches may take about 24 hours to refresh. See the [cleanup/guard record](../docs/CONTRIBUTOR-ATTRIBUTION-CLEANUP.md) for mappings, validation and recovery. Owners must not deliberately bypass the guards; other clones need to resynchronize before pushing.

### Just completed: Part 04 slice 1 — client records

The owner approved the design's safe positions as decision [C10](90-decisions-and-open-questions.md). Details, rules and code are in the [implementation record](04-client-records-and-imports.md#slice-1-implementation-record); evidence is in [PART-04-SLICE-1-VALIDATION.md](../docs/PART-04-SLICE-1-VALIDATION.md).

- **Screens:** Registrar → **Client records**: search by name or the start of an ID, filter by category and verification, create a client, and a detail page with correct details, add or retire an ID, verify, flag for review, and a full history.
- **Rules:** IDs are exact text (leading zeroes kept) and can never be reused, even retired ones; two people may share a name and are never merged; every change needs a reason and rejects a stale screen; correcting a verified profile returns it to "needs review"; the last current ID cannot be retired; the audit trail holds no names or full IDs.
- **Access:** view = Intake staff, Registrar processor, approver, release officer; change = Registrar processor only. Cashier, both supervisors, report exporter, supplier roles and the system administrator are denied.
- **Data:** 20 clearly fake demo clients (names end in "(Demo)", IDs start with "DEMO"). Your demo data was reset to this clean set after my testing.

### How to test it (about 10 minutes)

Sign in at http://localhost:8085 with `processor@bisu.test` (can change records) and `intake@bisu.test` (view only); passwords are in `src/Credentials.md`. If the demo clients are missing, run the two seeders in the README.

1. **Dashboard / sidebar:** a "Registrar" card and a "Client records" link appear for the processor and intake accounts, but not for the cashier or supervisors.
2. **Search:** try `DEMO-S-0001`, `DEMO-A`, `reyes` (two people share this name), `zoë`, `00123456` (leading zeroes) and `villanueva` (very long name). IDs are masked in the list.
3. **Open a record** to see the full ID and the history. Open "Rosa Villanueva (Demo)" (one person with a Student ID and an Alumni ID) and "Retired ID Case (Demo)" (a retired ID kept as history).
4. **New client:** create one. Then try the same ID again, in lower case; it is refused with a clear message.
5. **Correct details, Verify, Flag for review.** Correct a verified client's name and watch it return to "Needs review".
6. **Add ID, then Retire the old one.** Try to retire the only current ID; it is refused.
7. **Stale edit:** open the same client in two tabs, save in one, then save in the other; the second is refused and refreshes.
8. **View-only:** sign in as `intake@bisu.test`; no change buttons appear and `/registrar/clients/create` shows 403.
9. **Audit trail:** as `admin@bisu.test` (password then two-factor; see Credentials.md) open Administration → Audit trail. Client events appear with no names and only the last 4 characters of an ID.
10. **Phone width:** narrow the browser to about 390 px; the list and detail pages should not scroll sideways.

### Validation

- `./check.ps1` passed on the final code: **161 tests / 844 assertions** (64 new), Larastan level 7, formatting, lint, Vue TypeScript and production build.
- The whole suite also passed on a disposable **MySQL** database. That run exposed a too-long index name and four tests that assumed JSON key order (fixed; two were Part 03 tests). Parallel race probes on MySQL gave exactly one winner each for same-ID creation, stale saves, cross-profile ID adds and concurrent retirement, with no duplicates or version drift.
- A 34-check Edge walkthrough passed (desktop and phone) with no page errors. See the validation document for coverage and what is not covered.

### Decisions still needed

| Decision | Owner | Required answer |
|---|---|---|
| **After you test:** any change to slice 1 | Project owner | For example: may Registrar supervisors see client records? Should viewing a record be audited (also part of D09)? Should verification need a second person? |
| D04 | Registrar records custodian | The **official ID formats** (the current rule is a labelled demo placeholder), who maintains and verifies profiles, duplicate and correction authority, and a sample source for imports |
| D03 / D09 | Registrar + MIS + DPO | Identity proof and recovery (slice 3); which fields may be stored, retention, correction and erasure, access auditing |
| D05 | Registrar + Cashier | The Citizen's Charter, services, requirements and fees (Part 05) |
| D01, D13, D20 | Sponsor/offices + MIS | Release scope, import volume and worker resources, supported campus browsers |

### Next steps, in order

1. **You test slice 1** with the checklist above and tell me what to change. I will fix it on the same local branch.
2. **Take the ID question to the Registrar** (D04, questions R3 and R4 in the [office checklist](95-office-interview-checklist.md)). The demo ID rule (3–32 letters, digits or hyphens, case-insensitive) must be replaced by the approved format before any real record is entered. Changing it needs a versioned rule and a reviewed backfill.
3. **Next build, whichever input arrives first:**
   - **Part 05 (services and fees)** needs the Citizen's Charter, services and fees. I can write its design first, as was done for Parts 03 and 04.
   - **Part 04 slice 2 (imports)** needs the D04 answers, a sample source file, a queue worker and the D09 retention rules.
4. Publishing: when you want these branches pushed or merged, say so. Merge order: `docs/handoff-local-only-2026-10-17`, then `feat/part-04-client-records`.

### Blocked or waiting

- Imports and account linking wait for D03/D04/D09 and a configured queue worker (the dev Compose has none).
- Office acceptance of Parts 03 and 04 is still pending; everything so far is developer QA with synthetic data.

### Watch out

- The **demo ID rule is a placeholder**; do not enter real IDs until D04 is answered.
- `clients.import` is still in the shared Office supervisor role for both offices. Slice 2 must require the Registrar office so a Cashier supervisor is denied; the policy approach used in slice 1 already does this for view and change.
- MySQL stores JSON objects with their keys re-ordered. Compare audit and revision JSON in tests with `assertEquals`, not `assertSame`.
- Write every client change through `ClientProfileRecorder` (version, revision and audit together) and keep names and full IDs out of the audit trail.
- Keep PHP and TypeScript permission lists and the Part 03 catalogue in step. Install packages inside the containers; run `./setup.ps1` once after pulling the dependency-volume change.
- Do not commit credentials, real records or the walkthrough scripts. The unrelated untracked `src/nul` is left alone and excluded from commits.

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
