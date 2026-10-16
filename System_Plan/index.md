# BISU Registrar — System Plan and Progress Tracker

**Project:** Integrated Registrar–Cashier Document Request, Queuing, and Transaction Management System for Bohol Island State University – Balilihan Campus.

**Last updated:** 2026-10-17 · **Current stage:** Planning / requirements review · **Current focus:** Part 01 and the [office interview checklist](95-office-interview-checklist.md) · **Next development slice:** Parts 02–05 after their affected decisions are resolved.

This is the navigation and status source for the project. Each part contains the features to build, implementation steps, connections to other modules, acceptance criteria and evidence to collect. Drafting a plan does not mark its features implemented, tested or deployed.

## Confirmed project direction

- Extend the existing Laravel 13 + Vue 3/Inertia + TypeScript + Tailwind CSS/shadcn-vue application.
- **Host on BISU's local campus network. “Online” means browser access on approved BISU LAN/Wi-Fi.** Public Internet hosting and off-campus access are outside the initial scope.
- Support campus-network self-service and on-campus assisted/kiosk use, delivered in phases.
- Initially use a verified student/alumni list maintained/imported by authorized staff.
- Preserve the existing application foundation and document changes, checks and optimization suggestions.

## Plan at a glance

Who uses which module, and how a request moves between offices:

```mermaid
flowchart TB
    subgraph People["People on BISU LAN / Wi-Fi"]
        direction LR
        CL[Students and alumni] ~~~ RS[Registrar staff] ~~~ CS[Cashier staff] ~~~ AD[Admin and MIS]
    end
    subgraph Flow["Request flow"]
        direction LR
        PO["Portal and intake<br/>06"] --> QU["Queue<br/>07"] --> RW["Registrar<br/>08"]
        RW -->|endorse paid request| CW["Cashier<br/>09"]
        CW -->|financial clearance| RW
        RW --> DG["Documents<br/>10"] --> RT["Release and tracking<br/>11"]
    end
    subgraph Support["Supporting modules"]
        direction LR
        AC["Accounts, roles, audit<br/>03"] ~~~ RP["Reports<br/>12"] ~~~ VN["Vendor list<br/>16"]
    end
    subgraph Store["Campus storage, backed up"]
        direction LR
        DB[(MySQL)] ~~~ FS[(Private files)]
    end
    People --> Flow
    People --> Support
    Flow --> Store
    Support --> Store
```

| Who | Uses |
|---|---|
| Students and alumni | Portal (06), queue tickets (07), tracking and claiming (11) |
| Registrar staff | Queue counters (07), Registrar workspace (08), documents (10), release (11), vendor list (16), reports (12) |
| Cashier staff | Cashier queue (07), Cashier workspace (09), collection reports (12) |
| Admin and MIS | Accounts and roles (03), deployment and backups (14) |

Every module stores data in MySQL. Generated documents and attachments go to private files.

Build order by milestone. Each row must be working before the next row goes live:

```mermaid
flowchart TB
    subgraph M01["M0–M1 Foundation"]
        direction LR
        P00[00 Baseline] --> P01[01 Workflow] --> P02[02 Data model] --> P03[03 Accounts and audit]
        P03 --> P04[04 Client records]
        P03 --> P05[05 Services and fees]
    end
    subgraph M2["M2 First transaction"]
        direction LR
        P06[06 Portal and intake] --> P07a[07 Basic queue] --> P08[08 Registrar] --> P09[09 Cashier]
    end
    subgraph M3["M3 Full scope"]
        direction LR
        P10["10 Documents<br/>minimal path in M2"] --> P11["11 Release and tracking<br/>minimal path in M2"] --> P12[12 Reports]
        P07b[07 Kiosk and display] ~~~ P16[16 Vendor list]
    end
    subgraph M45["M4–M5 Acceptance"]
        direction LR
        P13[13 Testing and UAT] --> P14[14 Deployment] --> P15[15 Handover]
    end
    M01 --> M2 --> M3 --> M45
```

Part 16 technically needs only Parts 02–03, but is scheduled after M2 by decision C07. Iterations, decision gates and target dates are in [94](94-team-schedule-and-risks.md).

## Where we are now

| Area | Current status | Evidence |
|---|---|---|
| Laravel/Vue/Docker foundation | Implemented, repository published and verified for development | [Part 00](00-current-baseline.md), [existing setup report](../SETUP-VALIDATION.md) |
| Latest foundation validation | 40 tests / 138 assertions, frontend/PHP checks and clean-checkout GitHub CI passed on 2026-10-16 | [Validation report](../docs/FOUNDATION-VALIDATION.md) |
| Current local runtime | Verified on 2026-10-16; app/auth/Vite HTTP 200, MySQL/Redis healthy | [Foundation validation](../docs/FOUNDATION-VALIDATION.md) |
| Registrar/Cashier business modules | Not started | Inspected routes/model/migration inventory contains the starter foundation |
| Planning documentation | Draft reviewed 2026-10-17; gaps filled and diagrams added; needs team and process-owner review | [Plan review](93-plan-review.md); [change log](92-planning-change-log.md) |
| Campus staging | Not deployed / no deployment recorded | [Deployment register](14-campus-deployment-and-operations.md) |
| Campus production | Not deployed / no deployment recorded | No internal production URL, release revision or deployment approval recorded |

The configured developer URL is `http://localhost:8085`. It is not a BISU network production address. The campus hostname and network access details remain to be assigned by MIS.

## Part-by-part navigation

| Part | Plan / feature area | Implementation | Validation | Deployment |
|---|---|---|---|---|
| 00 | [Current application baseline](00-current-baseline.md) | Foundation and repository tooling implemented/published | Local checks and GitHub CI passed: 40 tests / 138 assertions | Local verified only |
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
| 16 | [Registrar vendor list and performance points](16-vendor.md) | Not started; scope confirmed (C07), build after M2 | Not started | Not deployed |

## Supporting references

- [Repository foundation validation](../docs/FOUNDATION-VALIDATION.md) - repository, local checks and CI evidence.

- [Original project concept brief](project-concept-brief.md) — preserved source, followed by confirmed scope clarification.
- [Decisions and open questions](90-decisions-and-open-questions.md) — confirmed choices, proposed defaults and office decisions still needed.
- [Requirement traceability](91-requirement-traceability.md) — where each objective, module and output is planned and checked.
- [Planning change log](92-planning-change-log.md) — changes to scope, assumptions and documentation.
- [Plan review 2026-10-17](93-plan-review.md) — gaps found, what changed and the team's check-off list.
- [Team, schedule and risks](94-team-schedule-and-risks.md) — roles, iterations, decision gates, thesis checkpoints, Definition of Done, risk register.
- [Office interview checklist](95-office-interview-checklist.md) — questions per office mapped to open decisions, and documents to request.
- [Glossary](96-glossary.md) — terms and abbreviations.
- [New-part template](99-part-template.md) — use when adding a future module or work package.

## Delivery sequence and milestones

Part numbers are navigation order; dependencies determine implementation order. Permissions/audit and module tests are built throughout, not postponed to the end.

| Milestone | Parts / scope | Exit condition | State |
|---|---|---|---|
| M0 — Development foundation | 00 | Starter app and setup checks | Local checks and GitHub CI passed on 2026-10-16 |
| M1 — Agreed rules and secure data foundation | 01–05 | Reviewed workflow; roles, verified client linkage/import and catalog implemented/tested | Planned |
| M2 — First integrated office transaction | 06–09; minimal approved output/release path from 10–11 | Campus portal + assisted intake; free and paid requests traverse both offices without bypasses | Planned |
| M3 — Complete functional scope | Remaining 07, 10–12; 16 | Kiosk/display, approved template set, tracking/release, notifications, all required reports and the Registrar vendor list validated | Planned |
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

- Team: check off the [plan review](93-plan-review.md), assign roles and dates in [94](94-team-schedule-and-risks.md), and name owners for every open decision.
- Take the [office interview checklist](95-office-interview-checklist.md) to the Registrar, Cashier, MIS, DPO and Supply/BAC.
- Review the office decisions in [Part 01](01-requirements-and-workflow.md) and the [decision register](90-decisions-and-open-questions.md).
- Confirm identity proof and initial import columns, first services/fee schedule, queue policy and payment/receipt procedures.
- Have MIS select the campus host, internal hostname and permitted LAN/Wi-Fi access.
- Repository foundation is verified and published. Implement the first secure client/catalog slice after affected workflow decisions are recorded.

The original planning delivery changed documentation only. Repository preparation on 2026-10-16 adds development tooling, CI and LAN-safe font handling; business feature implementation remains a separate scoped task.

## Published baseline

Repository: https://github.com/EnrieJohn31/BISU-Registrar. Tested foundation revision: 84db53477d2fb22492b1d6789c072c7e7c87f746. [Passing clean-checkout CI](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38878033808). Later documentation-only publication does not implement or deploy business features.
