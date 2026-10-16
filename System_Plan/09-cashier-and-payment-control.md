# Part 09 — Cashier assessments and payment control

[Back to index](index.md) | [Previous](08-registrar-workspace.md) | [Next](10-document-generation-and-approval.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft |
| Implementation | Not started |
| Validation | Not started |
| Deployment | Not deployed |
| Owner | Cashier process owner with developer; names pending |
| Dependencies | Parts 03, 05, 07–08; reporting integration in Part 12 |
| Updated | 2026-10-15 |

## Initial scope

Record and verify payments made through campus-approved payment procedures, attach official receipt/reference details, and return financial clearance to Registrar. Running on the BISU LAN does not require an online payment gateway. Integrating a gateway or issuing official fiscal receipts is outside the initial scope unless separately approved.

## Features and screens

- Cashier queue and pending assessments with client/request/service references.
- Assessment detail, approved fee breakdown and outstanding balance.
- Payment recording, verification, official receipt/reference, received date and verifying operator.
- Duplicate detection, discrepancy/hold handling and controlled reversal/adjustment history.
- Transaction/payment history and daily collections/reconciliation view.

## Financial state contract

| State | Meaning |
|---|---|
| `not_required` | Approved zero-fee assessment; not a fabricated paid transaction |
| `unpaid` | Valid assessed balance with no verified qualifying payment |
| `pending_verification` | Payment record/claim awaiting authorized validation; does not clear processing |
| `paid` | Verified, non-voided payments satisfy the full current assessment |
| `disputed` | Active discrepancy hold; release blocked pending resolution |

Payment records have their own `recorded`, `verified` and `voided` states. Refunds, waivers and fee adjustments are separate authorized records, never deletion of the original payment. Initial UI assumes full payment; partial payments, overpayments and refund approval rules remain a decision and cannot silently produce `paid` clearance.

## Implementation steps

1. Define assessment, payment, receipt-reference and adjustment schema with exact money arithmetic and source snapshots.
2. Build pending assessments and policy-controlled recording/verification actions.
3. Validate assessed amount, currency, receipt reference/date and office scope. Define receipt uniqueness using the approved issuer/series/year scheme, not an invented global rule.
4. Lock the assessment during verification, validate idempotency and recalculate verified balance in one database transaction.
5. Commit payment audit and status events atomically. Publish a durable clearance event only after commit; repeated delivery must not duplicate the Registrar transition.
6. Add authorized discrepancy holds and correction/reversal records with reasons. Recalculate clearance after any valid adjustment.
7. Block release if clearance is revoked before handover. If a payment issue is found after completed release, open an exception for authorized follow-up while preserving the historical release.
8. Reconcile daily verified collections with the official cashier source; surface unmatched records for review.

## Acceptance criteria / checks

- [ ] A payment claim or unverified entry cannot clear a request.
- [ ] Duplicate receipt submission, double-click verification and simultaneous operators cannot double-count collections.
- [ ] Amount/currency mismatches are rejected or routed to an explicit discrepancy action.
- [ ] Verified full payment advances the correct request once; another request is unaffected.
- [ ] Voids/adjustments preserve original records, actor, reason and reconciliation effect.
- [ ] Registrar users cannot impersonate Cashier verification through HTTP requests.
- [ ] Reports reconcile verified payments, reversals and approved adjustments using agreed date rules.

## Decisions / evidence

Resolve [D07](90-decisions-and-open-questions.md) before financial implementation: receipt authority, permitted payment channels, separation of duties, discounts/waivers, partial payments, overpayments, refunds and reconciliation rules. Use synthetic data for tests and retain Cashier acceptance of a paid transaction and a reversal/discrepancy scenario.
