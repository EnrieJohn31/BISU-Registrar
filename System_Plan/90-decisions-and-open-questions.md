# Decisions and open questions

[Back to index](index.md)

Updated: 2026-10-15. This register distinguishes owner-confirmed scope from proposed implementation defaults and institution-specific rules. Open decisions do not prevent writing or reviewing plans; they gate only the affected implementation or rollout.

## Confirmed decisions

| ID | Decision | Confirmed by / date | Effect |
|---|---|---|---|
| C01 | Application title: BISU Registrar; use existing Laravel/Vue/shadcn-vue/Docker foundation | Project owner, initial setup request | Extend existing project |
| C02 | Support both self-service portal and on-campus intake, delivered in phases | Project owner, 2026-10-15 | Shared workflow; portal/assisted intake first, kiosk follows as proposed sequence |
| C03 | Authorized staff maintain/import a verified student/alumni list initially | Project owner, 2026-10-15 | Build imports and controlled verification; no initial external database dependency |
| C04 | Host on BISU's local campus network; “online” means campus LAN browser access | Project owner, 2026-10-15 | Internal campus production, local assets, LAN access checks; no initial public Internet hosting |
| C05 | Maintain linked part plans and implementation/deployment navigation in `System_Plan/index.md` | Project owner, 2026-10-15 | Living Markdown plan and status tracker |

## Proposed defaults, awaiting process review

- One service type per request, with validated copy quantity; mixed-service carts and partial release deferred.
- Reuse Laravel/Inertia; avoid a separate frontend API application initially.
- Campus portal plus staff-assisted intake before shared-device kiosk deployment.
- Poll for queue/tracking updates initially; agree refresh interval and performance target.
- Campus display/service-day timezone `Asia/Manila`, storage timestamps UTC.
- Cashier records/verifies existing approved payment channels; no initial payment gateway.
- In-app notifications first; actual email delivery/account recovery depends on campus arrangements.
- FIFO within eligible queues until the campus approves priority rules.
- Remote-from-counter campus submissions receive reserved tickets; attendance/check-in controls active queue placement.

## Open decision register

| ID | Decision required | Decision owner to assign | Blocks / affected work | Current state |
|---|---|---|---|---|
| D01 | Initial services and first-release scope; confirm phased portal/assisted/kiosk order | Project sponsor + offices | Parts 01, 05–07; release sizing | Open; proposed sequence documented |
| D02 | Hosting machine/OS, internal hostname, HTTPS trust, VLAN/Wi-Fi access, Internet-independent operation | MIS | Part 14; campus staging/production | LAN hosting confirmed; infrastructure details open |
| D03 | Proof for identity linkage, unlinked account access, account recovery and staff provisioning | Registrar + MIS | Parts 03–04, 06; real-client access | Open |
| D04 | Import format/approved columns, source custodian, ID formats, duplicates and correction authority | Registrar records custodian | Part 04; reliable identity source | Maintained/imported list confirmed; format open |
| D05 | Approved services/requirements, effective fees, waivers, assessment timing and targets | Registrar + Cashier | Parts 05, 08–09 | Open; no fee amounts invented |
| D06 | Service hours/day, reserved-ticket validity, check-in, queue priorities, no-show/transfer and printing | Both offices | Parts 06–07 | Open |
| D07 | Receipt source/numbering, payment channels, verifier duties, partial/overpayments, adjustments/refunds and reconciliation dates | Cashier | Part 09; any real financial use | Open |
| D08 | Approved templates, record sources, signatories, separation of duties, document numbering and claimant/representative/reissue rules | Registrar | Parts 03, 08, 10–11 | Open |
| D09 | Privacy notices, permitted data, retention/deletion, audit access and incident responsibilities | Authorized campus privacy/MIS/office owners | Parts 03–04, 10–14; real-data rollout | Open; no legal-compliance claim |
| D10 | Counter count, kiosk/display devices, printers, accessibility, shared-device identity method | Offices + MIS | Part 07 and campus equipment testing | Open |
| D11 | Campus mail relay or assisted account process, notification channels, retry/freshness expectations | MIS + offices | Parts 03, 11, 14 | In-app proposed; actual mail delivery open |
| D12 | Academic periods, report layouts/metrics, financial date basis and export access | Registrar + Cashier | Part 12 | Open |
| D13 | Peak users/load, response targets, backup frequency/retention, recovery targets and monitoring ownership | MIS + offices | Parts 13–14 release acceptance | Open; no arbitrary service-level promise |
| D14 | Named UAT/release approvers, pilot scope, deployment window, training/support/maintenance ownership | Sponsor + MIS + offices | Parts 13–15 | Open |

## Decision entry template

```text
Decision ID:
Question / trigger:
Approved decision:
Approver and authority:
Decision date:
Evidence reference (no secrets or personal records):
Affected plan files / implementation files:
Migration or behavior impact:
Validation needed:
Supersedes decision:
```

When an assumption changes, update the workflow, data model, acceptance tests and release scope together. Keep the historical decision rather than silently rewriting its approval.
