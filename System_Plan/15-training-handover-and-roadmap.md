# Part 15 — Documentation, training, handover and improvements

[Back to index](index.md) | [Previous](14-campus-deployment-and-operations.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft |
| Implementation | Planning documents created; user/operations manuals not started |
| Validation | Training and handover acceptance not started |
| Deployment | Not deployed / not handed over |
| Owner | Project coordinator, office trainers and MIS support owner; names pending |
| Dependencies | Draft alongside Parts 03–12; finalize with Parts 13–14 |
| Updated | 2026-10-15 |

## Deliverables

- Student/alumni guide: campus network access, identity linking, request submission, queue/check-in, payment instructions, tracking and claiming.
- Staff-assisted intake and kiosk quick guides with privacy/session-reset instructions.
- Registrar guide: verification, returned requirements, holds, processing, approval, release and corrections.
- Cashier guide: assessments, receipts, verification, discrepancies, adjustments and reconciliation.
- Admin/MIS manual: roles, provisioning, imports, configuration, deployment, worker operation, backups, restoration, monitoring and incidents.
- Developer reference: architecture, schema, states, permissions, module connections, test commands, build/release process and approved decisions.
- Training materials, attendance/competency records, support contacts and signed handover checklist.

## Implementation steps

1. Assign the campus project sponsor, service owners, technical owner and support contacts; involve the IT Thesis Coordinator and faculty/advisers as appropriate to the project arrangement.
2. Produce short task-based guides as each feature becomes stable, using sanitized screenshots and actual approved process wording.
3. Train office users on the complete handoff, including free/paid requests, returns, payment discrepancies, no-shows, representative claims and outages.
4. Train MIS on a real isolated restore exercise, controlled deployment and role/account recovery.
5. Run a limited campus pilot with agreed scope and support coverage; record feedback, issues and reconciled office results.
6. Hand over repository access, deployment knowledge, template/fee ownership and secret recovery through approved secure channels. Never put passwords in manuals or this folder.
7. Agree maintenance ownership, release/change process, escalation path and periodic access/backup review.

## Handover acceptance

- [ ] Every expected output in the brief is implemented or explicitly deferred by a recorded decision.
- [ ] Registrar and Cashier users can complete assigned tasks without developer intervention.
- [ ] MIS can deploy, operate, restore and diagnose the system using the runbook.
- [ ] Support responsibility continues after student/thesis-team turnover.
- [ ] Known limitations and accepted defects have owners and next actions.
- [ ] Manuals match the actual deployed revision and internal hostname.
- [ ] The index, release ledger and change log reflect actual completion and deployment evidence.

## Optimization and future work candidates

These are suggestions, not approved initial scope:

| Candidate | When to consider | Required decision |
|---|---|---|
| Official student-system integration | Import maintenance becomes burdensome | Authorized interface, data owner and reconciliation design |
| WebSocket queue updates | Measured polling load or latency becomes material | Infrastructure/support cost and recovery behavior |
| Cached report aggregates | Measured reporting queries exceed agreed targets | Refresh rules, historical correctness and reconciliation |
| Additional approved templates | Offices validate initial generated outputs | Template/signatory ownership |
| Payment gateway integration | Campus authorizes a separate electronic collection process | Accounting, connectivity, provider and security requirements |
| Off-campus access | Institution requests it as a later expansion | New hosting/network, identity, exposure and support review |
| Multi-campus deployment | A formal expansion is approved | Data segregation, office scope and operational ownership |

## Completion evidence

Pending approved manuals, training dates/participants, pilot report, named support owner and handover acknowledgment. Development is not complete merely because source code has been delivered.
