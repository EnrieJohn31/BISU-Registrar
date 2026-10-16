# BISU Registrar — System Plan and Progress Tracker

**Project:** Integrated Registrar–Cashier Document Request, Queuing, and Transaction Management System for Bohol Island State University – Balilihan Campus.

**Last updated:** 2026-10-16 · **Current stage:** Planning / requirements review · **Current focus:** Part 01 · **Next development slice:** Parts 02–05 after their affected decisions are resolved.

This is the navigation and status source for the project. Each part contains the features to build, implementation steps, connections to other modules, acceptance criteria and evidence to collect. Drafting a plan does not mark its features implemented, tested or deployed.

## Confirmed project direction

- Extend the existing Laravel 13 + Vue 3/Inertia + TypeScript + Tailwind CSS/shadcn-vue application.
- **Host on BISU's local campus network. “Online” means browser access on approved BISU LAN/Wi-Fi.** Public Internet hosting and off-campus access are outside the initial scope.
- Support campus-network self-service and on-campus assisted/kiosk use, delivered in phases.
- Initially use a verified student/alumni list maintained/imported by authorized staff.
- Preserve the existing application foundation and document changes, checks and optimization suggestions.

## Where we are now

| Area | Current status | Evidence |
|---|---|---|
| Laravel/Vue/Docker foundation | Implemented for development | [Part 00](00-current-baseline.md), [existing setup report](../SETUP-VALIDATION.md) |
| Previous setup validation | 40 tests / 138 assertions and technical checks passed previously | Historical setup report; not rerun as part of planning |
| Current local runtime | Verified on 2026-10-16; app/auth/Vite HTTP 200, MySQL/Redis healthy | [Foundation validation](../docs/FOUNDATION-VALIDATION.md) |
| Registrar/Cashier business modules | Not started | Inspected routes/model/migration inventory contains the starter foundation |
| Planning documentation | Initial draft created; needs process-owner review | This folder; [change log](92-planning-change-log.md) |
| Campus staging | Not deployed / no deployment recorded | [Deployment register](14-campus-deployment-and-operations.md) |
| Campus production | Not deployed / no deployment recorded | No internal production URL, release revision or deployment approval recorded |

The configured developer URL is `http://localhost:8085`. It is not a BISU network production address. The campus hostname and network access details remain to be assigned by MIS.

## Part-by-part navigation

| Part | Plan / feature area | Implementation | Validation | Deployment |
|---|---|---|---|---|
| 00 | [Current application baseline](00-current-baseline.md) | Foundation and repository tooling implemented | 40 tests / 138 assertions and local technical checks passed; CI pending | Local verified only |
| 01 | [Requirements and shared workflow](01-requirements-and-workflow.md) | Not started; plan draft | Office review pending | Not deployed |
| 02 | [Architecture and data model](02-architecture-and-data-model.md) | Not started | Not started | Not deployed |
| 03 | [Accounts, permissions, privacy and audit](03-accounts-permissions-and-audit.md) | Not started beyond auth starter | Not started | Not deployed |
| 04 | [Verified client records and imports](04-client-records-and-imports.md) | Not started | Not started | Not deployed |
| 05 | [Services, requirements and fees](05-services-requirements-and-fees.md) | Not started | Not started | Not deployed |
| 06 | [Campus portal and assisted intake](06-portal-and-request-intake.md) | Not started | Not started | Not deployed |
| 07 | [Queues, counters, display and kiosk](07-queues-counters-and-kiosk.md) | Not started | Not started | Not deployed |
| 08 | [Registrar workspace](08-registrar-workspace.md) | Not started | Not started | Not deployed |
| 09 | [Cashier and payment control](09-cashier-and-payment-control.md) | Not started | Not started | Not deployed |
| 10 | [Document generation and approval](10-document-generation-and-approval.md) | Not started | Not started | Not deployed |
| 11 | [Release, tracking and notifications](11-release-tracking-and-notifications.md) | Not started | Not started | Not deployed |
| 12 | [Reporting and analytics](12-reporting-and-analytics.md) | Not started | Not started | Not deployed |
| 13 | [Testing and campus acceptance](13-testing-and-campus-acceptance.md) | Not started for modules | Not started | Not deployed |
| 14 | [Campus deployment and operations](14-campus-deployment-and-operations.md) | Dev Docker only | Operational checks pending | Not deployed |
| 15 | [Training, handover and improvements](15-training-handover-and-roadmap.md) | Manuals/training not started | Not started | Not handed over |

## Supporting references

- [Repository foundation validation](../docs/FOUNDATION-VALIDATION.md) - repository, local checks and CI evidence.

- [Original project concept brief](project-concept-brief.md) — preserved source, followed by confirmed scope clarification.
- [Decisions and open questions](90-decisions-and-open-questions.md) — confirmed choices, proposed defaults and office decisions still needed.
- [Requirement traceability](91-requirement-traceability.md) — where each objective, module and output is planned and checked.
- [Planning change log](92-planning-change-log.md) — changes to scope, assumptions and documentation.
- [New-part template](99-part-template.md) — use when adding a future module or work package.

## Delivery sequence and milestones

Part numbers are navigation order; dependencies determine implementation order. Permissions/audit and module tests are built throughout, not postponed to the end.

| Milestone | Parts / scope | Exit condition | State |
|---|---|---|---|
| M0 — Development foundation | 00 | Starter app and setup checks | Local checks passed on 2026-10-16; GitHub CI pending |
| M1 — Agreed rules and secure data foundation | 01–05 | Reviewed workflow; roles, verified client linkage/import and catalog implemented/tested | Planned |
| M2 — First integrated office transaction | 06–09; minimal approved output/release path from 10–11 | Campus portal + assisted intake; free and paid requests traverse both offices without bypasses | Planned |
| M3 — Complete functional scope | Remaining 07, 10–12 | Kiosk/display, approved template set, tracking/release, notifications and all required reports validated | Planned |
| M4 — Campus staging acceptance | 13–14; manuals from 15 | UAT, LAN-only tests, security checks, restore/rollback and office sign-off | Planned |
| M5 — Campus production and handover | 14–15 | Actual deployment evidence, pilot reconciliation, training and support ownership | Planned |

M2 is an integration milestone, not the complete concept-brief delivery. M3 covers the remaining agreed functional scope. Training material is drafted during development. No calendar commitment is assigned until scope, staff availability and equipment are confirmed.

## Status rules

| Dimension | Allowed values / meaning |
|---|---|
| Plan | Draft → Reviewed → Approved; approval requires named owner/date/evidence |
| Implementation | Not started → In progress → Implemented; use Blocked with exact dependency, or Deferred only with a recorded decision |
| Validation | Not started → In progress → Passed / Failed; Partially passed when failures or unexecuted cases remain |
| Deployment | Not deployed → Local verified → Campus staging → Campus production; record revision, URL, date and evidence per environment |

For governance/manual work, use Reviewed/Accepted rather than inventing a code deployment. A module may be implemented and still not deployed. Never mark production deployed based on a Docker build, local test or screenshot alone.

## How to update this tracker after each work session

1. Open the relevant part and complete only the checklist items actually finished.
2. Record changed files/migrations, implementation revision, test commands/results, unresolved issues and next action in that part.
3. Update that part's metadata and the corresponding row above in the same change.
4. Add scope/rule changes to the decision register and change log; update any affected downstream plans.
5. For deployments, update Part 14's release ledger and the environment status above with the exact deployed revision and evidence.
6. Record failures honestly, including a named owner or proposed next step. Do not include passwords, real student records, private documents or secret values.

## Immediate next actions

- Review the office decisions in [Part 01](01-requirements-and-workflow.md) and the [decision register](90-decisions-and-open-questions.md).
- Confirm identity proof and initial import columns, first services/fee schedule, queue policy and payment/receipt procedures.
- Have MIS select the campus host, internal hostname and permitted LAN/Wi-Fi access.
- Finish repository foundation verification/publication, then implement the first secure client/catalog slice after affected workflow decisions are recorded.

The original planning delivery changed documentation only. Repository preparation on 2026-10-16 adds development tooling, CI and LAN-safe font handling; business feature implementation remains a separate scoped task.
