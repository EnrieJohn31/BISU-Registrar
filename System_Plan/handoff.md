# Handoff

[Back to index](index.md) | [Change log](92-planning-change-log.md) | [Decision register](90-decisions-and-open-questions.md)

This file is **rewritten, not appended**, after every completed plan part or implementation slice. It always describes where the project stands now and what the next person should do. Dated history stays in the [change log](92-planning-change-log.md).

## Current handoff — 2026-10-17

**Stage:** Planning. **Milestone:** M1 (no business code yet). **Working branch:** `docs/part-03-accounts-design-2026-10-17`, created from the supplier-rename branch; not committed.

### Just completed: Part 03 slice 1 design (staff accounts, roles, audit)

Design only, no code. Read [Part 03, slice 1](03-accounts-permissions-and-audit.md#slice-1-design-staff-accounts-roles-and-audit):

- **RBAC approach:** native, enum-based roles recommended over spatie/laravel-permission (new decision D22), with a comparison table.
- **Roles and permissions:** 10 staff roles, and a permission catalogue covering every part so the offices review it once. Clients are not a role; they act through a verified ID link (Part 04).
- **Data model:** field-level design for `offices`, `user_roles`, `audit_events`, and new `users` columns.
- **Diagrams:** request authorization flow, staff provisioning without email, account lifecycle.
- **Implementation detail:** audit trail design, proposed changes to the starter app, screens, code layout, and 14 slice tests.
- **Connected updates:** D22 added and D03 widened in the decision register; Parts 00 and 02 linked to the design; index updated; the Definition of Done in 94 and `AGENTS.md` now require rewriting this file.

### Decisions needed before coding slice 1

| # | Question | Recommendation | Who decides | Answer |
|---|---|---|---|---|
| 1 | D22: native enum-based roles or spatie/laravel-permission? | Native | Team (backend lead) | |
| 2 | Remove self-service account deletion now? | Yes; clients ask the office to close an account | Team; DPO later for client erasure (D09) | |
| 3 | Staff who are also alumni: one account or two? | One account, with "never act on your own request" enforced | Team, then Registrar | |
| 4 | Staff idle timeout, and which roles need 2FA or a passkey | 30 minutes; roles marked in Part 03's role table | Team proposes, offices and MIS confirm (D03) | |
| 5 | Temporary password expiry, or a printed activation code? | 72-hour temporary password | Team proposes, MIS confirms (D03) | |
| 6 | Who is the first system administrator? | An MIS staff member | MIS | |
| 7 | Do the role names and permission holders match how the offices work? | Show the catalogue at the Registrar and Cashier meetings | Registrar, Cashier (D03, D07, D08) | |

Questions 1–3 and 6 are enough to start coding with the proposed defaults. Questions 4, 5 and 7 can be confirmed while coding, because the values are configurable or kept in one map.

### Next steps, in order

1. **Team review:** read Part 03 slice 1, answer the table above, and record the answers in [90](90-decisions-and-open-questions.md) and Part 03.
2. **Kickoff items still open:** assign team roles and dates in [94](94-team-schedule-and-risks.md); work through the check-off list in [93](93-plan-review.md).
3. **Office meetings** ([95](95-office-interview-checklist.md)): adviser T1–T4; Registrar R1–R5, bringing the permission catalogue; MIS M1 and M3; DPO P1.
4. **Code slice 1** on a feature branch (suggested `feat/part-03-accounts-roles-audit`), following the design and the Definition of Done in 94. Then rewrite this file.
5. **After slice 1:** Part 05 (services and fees) if the Citizen's Charter is in hand, then Part 04 (client records and imports).

### Blocked or waiting

- Every M1 office decision is still open: D01, D03, D04, D05, D09, D20. Slice 1 needs only D22 and the team answers above; office input refines it but does not block it.
- The supplier-rename PR (`docs/rename-vendor-to-supplier-2026-10-17`) is not yet merged into `main`. This branch builds on it, so merge that PR first.

### Watch out

- Replace the shared `auth.user` with an explicit allow-list **before** adding columns to `users`. Otherwise the new columns (deactivation reason and others) are sent to every page.
- Removing self-deletion changes existing Settings tests and the `DeleteUser.vue` component. Update both in the same PR.
- Deactivation must block all sign-in paths: password, two-factor challenge, passkey and remember-me. Test each one.
- On Windows, `./check.ps1` may need `powershell -NoProfile -ExecutionPolicy Bypass -File ./check.ps1`.

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
