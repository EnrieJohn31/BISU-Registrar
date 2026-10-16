# Part 08 — Registrar review and processing workspace

[Back to index](index.md) | [Previous](07-queues-counters-and-kiosk.md) | [Next](09-cashier-and-payment-control.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft |
| Implementation | Not started |
| Validation | Not started |
| Deployment | Not deployed |
| Owner | Registrar process owner with developer; names pending |
| Dependencies | Parts 03–07; Parts 09–11 finish the paid/document/release workflow |
| Updated | 2026-10-15 |

## Features and screens

- Worklists by status, service, date, assignee and age; office dashboard with actionable counts.
- Request detail with verified identity, requirement checklist, attachment access and public/internal timelines.
- Record-check outcomes, returned requirements, rejection reason and controlled cancellation.
- Assignment/reassignment, internal notes, holds and processing checklists.
- Fee assessment/endorsement to Cashier and visible payment clearance.
- Document preparation, approval handoff and readiness for release.

## Implementation steps

1. Implement scoped paginated worklists using request policies and indexed filters.
2. Add a record-verification action that records actor, time, evidence/reference and outcome. The initial verified client list is not proof that every requested academic fact is correct.
3. Add `awaiting_client` with a specific reason and required next action; keep internal notes out of client-facing messages.
4. Introduce assignment/version checks so stale screens cannot overwrite another staff member's work.
5. Create an authoritative fee assessment and a linked Cashier task for paid services; free services follow the documented zero-payment branch.
6. Accept payment-clearance events from Part 09; Registrar cannot set a request to paid by editing its status.
7. Add processing and approver review connected to the immutable output/version in Part 10.
8. Enable ready-for-release only when records, required outputs, approval and financial clearance are valid and no active hold exists.

## Operating rules

- Each transition must use the shared workflow contract and a server-authorized action.
- Assignment helps coordinate work but does not grant additional permissions.
- Rejection and returns are distinct: a rejected request is terminal; returned requirements allow the same request to resume.
- Editing document inputs after approval invalidates that approval and creates a new draft/version.
- An active payment or records hold blocks approval/release, even if the screen previously showed clearance.
- Completed requests remain historical. Reissues or substantive corrections are linked transactions with their own authorization.

## Acceptance criteria / checks

- [ ] Registrar can process a free request from review to approval without a fake payment.
- [ ] A paid request cannot pass financial clearance before Cashier verification.
- [ ] Missing records return a clear client task and resume without duplicate requests.
- [ ] Two staff actions from stale screens cannot silently overwrite each other.
- [ ] Approval and release guards remain enforced through direct HTTP calls.
- [ ] Client-visible histories exclude internal comments and restricted evidence.

## Decisions / completion evidence

Pending approved record-check procedure, assignment rules and approver authority in [D05 and D08](90-decisions-and-open-questions.md). Complete with Registrar acceptance of one free, one paid, one returned and one rejected synthetic transaction.
