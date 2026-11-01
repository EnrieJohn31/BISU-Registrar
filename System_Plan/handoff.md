# Handoff

[Project index](index.md) | [Waiting-work list](97-work-while-waiting.md) | [Decisions](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

## Current position — 2026-11-01

**DEV-9 UI refresh is published through [PR #12](https://github.com/EnrieJohn31/BISU-Registrar/pull/12) for merge into `main` (C19–C21): five code commits plus docs commits, each stating what it changed.** GitHub records the final head, checks and merge result; use up-to-date `main` afterwards. The offices have not sent answers yet, so the owner chose UI work for now. `main` holds the merged release from PR #11 (Part 04 and the waiting-work slices). Part 05 still needs the owner's design approval (proposed C15) before any build. Campus deployment is still pending.

The owner reported the **phone test (OPS-1) done** on 2026-11-01. Phone model/OS/browser versions and whether a second laptop was checked were not recorded. The LAN preview was stopped; normal loopback development is running.

### Project status at a glance

| Area | Where it stands | What is next / still gated |
|---|---|---|
| Foundation / Part 03 accounts, roles and audit | Implemented; merged (`fa2246a`) | Institutional UAT and campus rollout pending |
| Part 04 slice 1 client records | Implemented, merged via PR #11 | Official ID/data/privacy/custodian acceptance remain D04/D09 |
| Part 04 imports and account linking | Design only | D03/D04/D09 and separately scoped feature work |
| DEV-2 worker, scheduler, Mailpit | Complete, merged | Campus mail/operations decisions still open |
| DEV-3 saved browser regression | Complete: 34 cases × four projects; passed again for DEV-9 | Extend when later features are scoped; physical device acceptance remains D20 |
| DEV-4 PDF renderer comparison | Complete; provisional Dompdf direction | Real templates/fonts/printer require D08 acceptance |
| DEV-8 BISU seal and browser icons | Complete, merged | — |
| **DEV-9 UI refresh** | **Done:** navy/gold theme from the seal, rebuilt welcome and sign-in pages, phone card lists, dashboard shortcuts and client totals, shared page header, empty states, skip link | Owner review of the look; published through PR #12 (C21). [Report and commit list](../docs/DEV-9-UI-REFRESH.md) |
| OPS-1 same-network phone access | Done (owner-reported 2026-11-01) | Record device/browser versions and a second-laptop check under D20 when known |
| Part 05 services, requirements and fees | Slice 1 design written (DEV-5), not built | **You approve the design (proposed C15)**, then DEV-6 builds the staff catalog on synthetic data |
| Part 07 basic queue | Not implemented | DEV-7 needs its own short design and approval |
| Portal, Registrar and Cashier workflows; documents/release/reports | Not implemented | Office answers and each part's design/approval gates |
| Campus staging / production | Not deployed | MIS infrastructure, acceptance and release approval |

### Latest slice — DEV-9 UI refresh (C19)

You approved all four proposed UI parts with the navy and gold direction. Presentation only: no business rule, permission, migration or route name changed.

- **Colours:** tokens in `src/resources/css/app.css` sampled from the seal (navy `#000066`, gold `#FFCC00`). Light mode leads with navy, dark mode with gold. Light-mode focus rings are navy, not gold as previewed, because gold on white is too faint (about 1.6:1). Every text pair meets WCAG AA in both themes.
- **Public pages:** new welcome page and sign-in panel built around the seal (a 320px derivative from the existing icon script). Login says "Sign in", has visible labels and natural tab order, and shows "Sign up"/"Register" only when registration is enabled.
- **Phone lists:** client records, users and audit trail rows become cards below 768px. The markup stays one table, so the saved browser tests and screen readers read the same rows.
- **Dashboard:** new `DashboardController`. Shortcut tiles follow the same policies as their routes; client totals by verification state (no names) for staff allowed to view client records; the tiles open the list with the matching filter.
- **Consistency/accessibility:** shared `PageHeader` (one h1 per page) and `EmptyState`; skip-to-content link; keyboard-reachable show-password button; visible From/To labels on audit date filters; "Overview" sidebar group; avatar initials ignore "(Demo)".

Validation: `./check.ps1` passed 174 tests / 1,055 assertions (build, lint, TypeScript, Pint, Larastan); all 136 saved browser checks passed with zero retries; 17 of 56 review captures were inspected directly, leading to four fixes. Details in the [DEV-9 report](../docs/DEV-9-UI-REFRESH.md#validation).

### Next action

1. **Review the new look** at http://localhost:8085 (light and dark; resize to phone width). Say what to change; colours are all in `app.css`. Start further UI changes on a new branch from up-to-date `main`.
2. **You review the [Part 05 design](05-services-requirements-and-fees.md#slice-1-design-catalog-core-dev-5-2026-10-18)** and approve, change or reject the seven proposals (including `services.view`). On approval I record C15 and build DEV-6 on a new local branch.
3. **Start the baseline (OF-4):** agree the method with the adviser, inform the Data Protection Officer and get each office head's signature (page 2 of the [kit](baseline-kit.pdf)) before observing.
4. **Send or follow up the [requirements checklist](requirements.pdf)**, starting with its "six to get first". The rest of the Citizen's Charter unlocks Part 05 slice 2.
5. **Alternative or parallel:** DEV-7 (basic queue) needs its own short design first. Thesis and office-preparation tasks in [97](97-work-while-waiting.md) need no code.
6. Optional UI follow-ups listed in the DEV-9 report: stronger input borders (WCAG 3:1), card layout for the remaining detail-page tables.

### Office decisions still needed

| Decisions | Owner | Still needed |
|---|---|---|
| D03 / D04 / D09 | Registrar + MIS + DPO | Linking/recovery proof, official IDs, approved import source/reviewer/correction authority, permitted fields, privacy/retention/access auditing |
| **C15 (proposed)** | Project owner | Approve, change or reject the seven Part 05 slice 1 proposals, including `services.view` |
| D05 / D07 | Registrar + Cashier | Approved services/requirements, fees, financial rules and complete charter source; the eight questions in the Part 05 design |
| D08 | Registrar | Approved templates/fonts, signatories, signatures, numbering, release rules and actual output/printer acceptance |
| D10 / D11 / D13 / D20 | MIS + offices | Kiosk/display devices and accessibility, campus mail/assisted delivery, workload targets, supported devices/browsers (record the OPS-1 phone details here) |
| D02 / D14 | MIS + sponsor + offices | Campus infrastructure, staging/release owners and deployment approval |

### Runtime and watch-outs

- Normal development restored 2026-11-01 with `./lan-preview.ps1 -Stop`: app http://localhost:8085, Vite http://localhost:5178, captured email http://localhost:8026; seven services. For phone access again, double-click `start-lan.cmd` (see [instructions](../docs/LAN-PHONE-PREVIEW.md)) and finish with `-Stop`. Remove any preview firewall rule you added.
- The isolated browser suite downloads Composer/npm packages on every run. A slow connection made one attempt time out on 2026-11-01 (GitHub download, not a test failure); rerun when the network is stable.
- Keep real records, private documents, secrets, runtime data and backups out of Git. Preserve human commit authorship and the attribution/push guards. Commit locally by default; C20 and C21 covered this branch and PR #12 only. Any further push, pull request or merge needs an explicit request.
- Builds with registration disabled still fail because `Register.vue` (and the welcome/login pages, which now hide the links but still import the route) import a route that generation omits. Record a separate technical fix before using that setting.
- New pages should use `PageHeader`, the semantic colour tokens (not raw hex or palette classes) and `.stack-table` with `data-label` cells for lists. Dashboard tiles must follow the target route's policy, not the shared permission list, because that list is not office-aware.
- For Part 05: enforce the office in the policy, name composite indexes explicitly (MySQL 64-character limit), compare JSON in tests with `assertEquals`, and keep PHP and TypeScript permission lists in step.
- Dompdf needs print-specific CSS; real templates, printer behaviour, private storage and queued retries need their own design/acceptance (DEV-4 report).
