# Part 13 — Integration testing and campus acceptance

[Back to index](index.md) | [Previous](12-reporting-and-analytics.md) | [Next](14-campus-deployment-and-operations.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft |
| Implementation | Not started for business modules |
| Validation | Starter results are historical; module acceptance pending |
| Deployment | Not deployed |
| Owner | Developer/QA with Registrar, Cashier and MIS acceptance owners; names pending |
| Dependencies | Tests accompany Parts 03–12; final acceptance requires all in-scope features |
| Updated | 2026-10-15 |

## Approach

Test each module when it is built, then validate a complete request across both offices in an isolated campus staging environment. Use synthetic records initially. Real institutional data may be used only under the approved access/process arrangement and must never enter source control or general test logs.

## End-to-end acceptance scenarios

| ID | Scenario | Required result |
|---|---|---|
| UAT-01 | Verified student submits a free request through campus portal | Ticket → review → processing → approval → identity-checked release → one completion |
| UAT-02 | Verified alumnus submits a paid request | Assessment → Cashier verification → clearance → approved output → release |
| UAT-03 | Staff-assisted submission | Same rules as portal; correct client owner and operator provenance |
| UAT-04 | Kiosk intake and next client | Valid ticket; previous client's data/session inaccessible after reset |
| UAT-05 | Unknown ID or account-link mismatch | No private data disclosed; explicit authorized review path |
| UAT-06 | Missing requirement, then client correction | Clear return reason; same request resumes with full history |
| UAT-07 | Duplicate submit/receipt and two simultaneous staff actions | One request/payment/action; no conflicting counter or release assignment |
| UAT-08 | Payment discrepancy or void before release | Financial hold blocks release; authorized resolution preserves history |
| UAT-09 | Payment discrepancy discovered after release | Historical release preserved; exception and financial adjustment are auditable |
| UAT-10 | Document template update / content correction | Historical approved copy preserved; changed content requires new approval |
| UAT-11 | Unauthorized direct URL, export or attachment access | Server rejects access across client and office boundaries |
| UAT-12 | Cancellation, rejection, no-show and requeue | Correct independent request/payment/queue histories and report counts |
| UAT-13 | Campus Internet disconnected while LAN remains available | Core portal, queues, payments, generation and release still work |
| UAT-14 | LAN/server interruption and restoration | Stale display warning, documented manual fallback, safe retries/reconciliation |
| UAT-15 | Daily/monthly/semester/academic-year reporting | Approved definitions and totals reconcile with sample transactions |
| UAT-16 | Backup restored to isolated environment | Database, files and authorized access consistent; issued outputs recoverable |

## Development checks

Run from the project root with Docker available:

```powershell
docker compose -f docker-compose.dev.yml exec app php artisan test
docker compose -f docker-compose.dev.yml exec app composer lint:check
docker compose -f docker-compose.dev.yml exec app composer types:check
docker compose -f docker-compose.dev.yml exec app npm run types:check
docker compose -f docker-compose.dev.yml exec app npm run check
docker compose -f docker-compose.dev.yml exec app npm run build
```

Build assets before rendering tests if the test harness uses the Vite manifest. Add a separate disposable MySQL test database for unique constraints, migrations, queue claims, payment locks and release races; never point tests that reset tables at operational data. The current starter suite uses SQLite in memory.

## Nonfunctional checks

- Agree on peak concurrent users, request volume, expected response time, queue-display freshness and document-generation time with MIS before assigning pass/fail thresholds.
- Measure on representative campus workstations, Wi-Fi/LAN segments, printers and kiosk equipment.
- Check keyboard navigation, readable contrast, mobile forms, validation focus and screen-reader labels.
- Check dependency vulnerabilities during implementation/release; record remediation decisions, not blanket guarantees.
- Exercise worker retry/failure, notification duplication, storage exhaustion, backup failure and audit recording.

## Release exit criteria

- [ ] All required UAT cases have evidence and office acceptance.
- [ ] No unresolved defect can corrupt payments, authorize an incorrect release, expose private records or prevent core use.
- [ ] Remaining lower-impact issues have a named owner and explicit acceptance.
- [ ] Permission, audit, private-file and MySQL concurrency checks pass.
- [ ] Restore drill and rollback rehearsal are documented.
- [ ] Required manuals, support ownership and production decisions are ready.

## Evidence record template

For each case record: case ID, release revision, environment, date, tester, expected result, actual result, sanitized evidence location, defect reference, retest outcome and accepting office. Update the index only from these results; do not mark a feature tested merely because its page renders.
