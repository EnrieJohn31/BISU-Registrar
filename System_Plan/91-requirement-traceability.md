# Requirement traceability

[Back to index](index.md) | [Source brief](project-concept-brief.md) | [Acceptance plan](13-testing-and-campus-acceptance.md)

Updated: 2026-10-17. Every row below is **planned, not business-feature complete**. The developer foundation is documented separately in Part 00. IDs provide stable references for future tickets, tests and acceptance evidence.

## Objectives and module requirements

| ID | Requirement from the brief | Planned location | Acceptance evidence |
|---|---|---|---|
| R01 | Centralized student/alumni service requests | [04](04-client-records-and-imports.md), [06](06-portal-and-request-intake.md) | UAT-01–03; shared request history |
| R02 | Student/Alumni ID identification and profile verification | [03](03-accounts-permissions-and-audit.md), [04](04-client-records-and-imports.md) | UAT-05; identity/ownership and import tests |
| R03 | Service selection and request submission | [05](05-services-requirements-and-fees.md), [06](06-portal-and-request-intake.md) | Catalog/version validation and duplicate-submit checks |
| R04 | Queue number generation and organized frontline queues | [07](07-queues-counters-and-kiosk.md) | UAT-04, 07, 12; MySQL concurrency and counter walkthrough |
| R05 | Registrar request management and record verification | [08](08-registrar-workspace.md) | UAT-01, 02, 06; office checklist review |
| R06 | Integrated Registrar/Cashier workflow | [01](01-requirements-and-workflow.md), [08](08-registrar-workspace.md), [09](09-cashier-and-payment-control.md) | UAT-02; guarded, audited handoff |
| R07 | Cashier payment monitoring, verification and updating | [09](09-cashier-and-payment-control.md) | UAT-07–09; receipt/amount/reversal checks |
| R08 | Document processing and generation | [08](08-registrar-workspace.md), [10](10-document-generation-and-approval.md) | UAT-10; approved template output comparison |
| R09 | Certifications, request forms, authentication/CAV-related and other approved documents | [05](05-services-requirements-and-fees.md), [10](10-document-generation-and-approval.md) | Office-approved inventory and per-template acceptance |
| R10 | Approval and release monitoring | [10](10-document-generation-and-approval.md), [11](11-release-tracking-and-notifications.md) | UAT-01, 02, 08, 10; claimant and clearance guards |
| R11 | Timely request status monitoring and transaction history | [06](06-portal-and-request-intake.md), [11](11-release-tracking-and-notifications.md) | Ownership, ordered timeline and agreed freshness tests |
| R12 | Daily and monthly reports | [12](12-reporting-and-analytics.md) | UAT-15; verified date-boundary totals |
| R13 | Semester and academic-year reports | [12](12-reporting-and-analytics.md) | Approved calendar fixtures and period totals |
| R14 | Clients served and most requested services | [12](12-reporting-and-analytics.md) | Distinct clients vs visits/requests/copies checks |
| R15 | Transaction status and Registrar statistics | [12](12-reporting-and-analytics.md) | Historical cutoff and worklist reconciliation |
| R16 | Cashier collections statistics and payment history | [09](09-cashier-and-payment-control.md), [12](12-reporting-and-analytics.md) | Cashier reconciliation including reversals |
| R17 | Role-based access and account management | [03](03-accounts-permissions-and-audit.md) | UAT-11; server-side policy and escalation tests |
| R18 | Audit trail and accountability | [03](03-accounts-permissions-and-audit.md), all business modules | Event/actor/reason coverage and audit-access tests |
| R19 | Secure student/alumni information and authorization controls | [03](03-accounts-permissions-and-audit.md), [13](13-testing-and-campus-acceptance.md), [14](14-campus-deployment-and-operations.md) | UAT-11, 13, 16; campus-reviewed privacy/retention process |

## Expected outputs and project-owner clarifications

| ID | Output / confirmed requirement | Planned location | Completion evidence |
|---|---|---|---|
| O01 | Functional campus-approved web system | [13](13-testing-and-campus-acceptance.md), [14](14-campus-deployment-and-operations.md) | UAT acceptance and actual release ledger |
| O02 | Centralized transaction database | [02](02-architecture-and-data-model.md) | Schema/migrations, constraints and restore drill |
| O03 | Student/alumni request interface | [06](06-portal-and-request-intake.md) | Portal and client ownership walkthrough |
| O04 | Registrar and Cashier dashboards | [08](08-registrar-workspace.md), [09](09-cashier-and-payment-control.md), [12](12-reporting-and-analytics.md) | Office-specific worklist/report acceptance |
| O05 | Queuing functionality | [07](07-queues-counters-and-kiosk.md) | Concurrent counters and privacy-safe display |
| O06 | Document generation functionality | [10](10-document-generation-and-approval.md) | Approved templates and historical output recovery |
| O07 | Reporting/analytics dashboard | [12](12-reporting-and-analytics.md) | Reconciled required reports |
| O08 | User/access management | [03](03-accounts-permissions-and-audit.md) | Role/provisioning/deactivation tests |
| O09 | Audit trail | [03](03-accounts-permissions-and-audit.md) | Auditable complete request and sensitive action coverage |
| O10 | System documentation and user manuals | [15](15-training-handover-and-roadmap.md) | Reviewed manuals matching release revision |
| O11 | Training/orientation | [15](15-training-handover-and-roadmap.md) | Training and handover records |
| C02 | Both self-service and on-campus intake in phases | [06](06-portal-and-request-intake.md), [07](07-queues-counters-and-kiosk.md) | Portal, assisted intake and kiosk UAT |
| C03 | Staff-maintained/imported verified client list initially | [04](04-client-records-and-imports.md) | Authorized import dry-run and conflict handling |
| C04 | BISU campus-network hosting; online means LAN access | [14](14-campus-deployment-and-operations.md) | Internal HTTPS, intended-device reachability and Internet-disconnected checks |
| C07 | Registrar supplier list with per-delivery performance points (owner addition, not in the original brief) | [16](16-supplier.md) | UAT-17–21; formula, transition and permission tests |

## Nonfunctional requirements (added 2026-10-17)

| ID | Requirement | Planned location | Acceptance evidence |
|---|---|---|---|
| N01 | Core services work on the campus LAN with the Internet disconnected | [02](02-architecture-and-data-model.md), [14](14-campus-deployment-and-operations.md) | UAT-13 |
| N02 | Performance and capacity meet targets agreed with MIS | [13](13-testing-and-campus-acceptance.md), [14](14-campus-deployment-and-operations.md) | D13 thresholds; measurement on campus hardware |
| N03 | Backups, recovery and continuity | [14](14-campus-deployment-and-operations.md) | UAT-16; restore drill against D13 targets |
| N04 | Security: access control, sessions, throttling, uploads | [03](03-accounts-permissions-and-audit.md), [04](04-client-records-and-imports.md), [06](06-portal-and-request-intake.md), [13](13-testing-and-campus-acceptance.md) | UAT-11; Part 13 security checklist |
| N05 | Privacy by design: minimization, notice, retention | [03](03-accounts-permissions-and-audit.md), [04](04-client-records-and-imports.md), [12](12-reporting-and-analytics.md) | D09; completed PIA; export audit review |
| N06 | Usability and accessibility: keyboard, contrast, mobile, kiosk | [06](06-portal-and-request-intake.md), [07](07-queues-counters-and-kiosk.md), [13](13-testing-and-campus-acceptance.md) | Accessibility checks; office walkthroughs |
| N07 | Works on the supported campus browsers and devices | [13](13-testing-and-campus-acceptance.md), [14](14-campus-deployment-and-operations.md) | D20 device matrix with test dates |
| N08 | Maintainable after the thesis team leaves | [15](15-training-handover-and-roadmap.md), [94](94-team-schedule-and-risks.md) | D21; developer reference; Definition of Done followed |

## Plan additions from the 2026-10-17 review

| ID | Addition | Planned location | Acceptance evidence |
|---|---|---|---|
| A01 | Citizen's Charter alignment: services, steps, processing times | [05](05-services-requirements-and-fees.md), [12](12-reporting-and-analytics.md) | D05; charter-based catalog review; turnaround report |
| A02 | Client feedback (CSM-aligned, if adopted) | [11](11-release-tracking-and-notifications.md), [12](12-reporting-and-analytics.md) | D19; one-feedback-per-request tests; results report |
| A03 | Legally required priority lanes | [07](07-queues-counters-and-kiosk.md) | D06; lane alternation walkthrough |
| A04 | Stalled requests and unclaimed documents | [01](01-requirements-and-workflow.md), [08](08-registrar-workspace.md), [11](11-release-tracking-and-notifications.md), [12](12-reporting-and-analytics.md) | D18; reminder and lapse tests; aging report |
| A05 | Go-live cutover with a parallel run | [14](14-campus-deployment-and-operations.md) | Recorded baselines; signed reconciliation |
| A06 | Repository custody and post-thesis ownership | [15](15-training-handover-and-roadmap.md) | D21; transfer record |
| A07 | Privacy program items: DPO, PIA, notice versions, breach contacts | [03](03-accounts-permissions-and-audit.md), [06](06-portal-and-request-intake.md) | D09; PIA sign-off |

## Collaboration and benefit evaluation

The brief identifies the IT Thesis Coordinator, faculty/advisers, Registrar, Cashier and campus MIS/IT as collaborators. Part 15 assigns their review, training and ownership responsibilities once named representatives are available.

Expected benefits are goals to measure, not promised outcomes: establish pre-pilot baselines for waiting time, processing time, repeat encoding, return visits, unresolved requests and report preparation effort; compare the pilot using agreed definitions and privacy-safe aggregates. Record the results in the pilot report rather than declaring improvement solely because the system is installed.
