# Part 00 — Current application baseline

[Back to index](index.md) | [Next: requirements and workflow](01-requirements-and-workflow.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft, based on inspected files |
| Implementation | Foundation and Part 03 merged; Part 04 and completed waiting-work slices included in [release PR #11](https://github.com/EnrieJohn31/BISU-Registrar/pull/11); transaction modules not started |
| Validation | Fresh local release foundation: 167 tests / 944 assertions; [release evidence and GitHub checks](../docs/RELEASE-2026-10-18.md). Historical foundation/Part 03 evidence below. Office acceptance pending |
| Deployment | Local Docker configured; staging and production not recorded |
| Owner | Project developer / MIS, assignment pending |
| Dependencies | None |
| Updated | 2026-10-18 |

## Purpose

Establish the starting point so future work extends the existing application and preserves its working connections. This plan set describes future development; creating a planning file does not implement its features.

## What exists

| Area | Evidence / current state |
|---|---|
| Backend | Laravel 13; setup lockfile resolved framework 13.36.0; PHP 8.4 in Docker |
| Frontend | Vue 3, Inertia 3, TypeScript, Tailwind CSS 4, shadcn-vue, Vite |
| Authentication | Starter login, registration, email verification, password recovery, account settings, two-factor/passkey support |
| Screens | BISU-branded welcome/authentication, role-aware dashboard, staff Users/role/credential screens, audit viewer and client records; DEV-8 replaces shared/browser starter marks with the campus-hosted BISU seal |
| Database | MySQL 8.4; starter migrations plus offices, account status, role assignments, audit events and client profiles/identifiers/revisions |
| Docker | app, node/Vite, MySQL, Redis, worker, scheduler and Mailpit in `../docker-compose.dev.yml`; separate disposable browser project in `../docker-compose.browser.yml` |
| URLs | Configured local app: `http://localhost:8085`; Vite: `http://localhost:5178` |
| Application location | `../src/`; Docker files and setup script live at project root |
| Existing evidence | [Setup validation](../SETUP-VALIDATION.md), [setup instructions](../README.md) |

The previous setup report records 40 passing tests / 138 assertions, passing frontend and PHP checks, successful migrations, and successful HTTP checks. These are historical setup results, not tests of registrar features.

On 2026-10-15 the planning inspection could not contact the Docker Desktop Linux engine. That inspection did not establish service availability. On 2026-10-16 Docker Desktop was started for repository preparation; updated results are recorded in [foundation validation](../docs/FOUNDATION-VALIDATION.md). A local Docker configuration or a compiled asset build does not establish a campus production deployment.

## Features that still need implementation

- Client identity linkage and reviewed imports. Manual client records on synthetic data are implemented in Part 04 slice 1.
- Later modules must enforce their own office/ownership rules and business audits; Part 03 staff roles and audit foundation are implemented.
- Service catalog, fees, document requests and request histories.
- Registrar and Cashier workspaces and their coordinated workflow.
- Office queues, counters, tickets and public display.
- Payments, documents, approvals, release records and reporting.
- Operational deployment, backup restoration, manuals and training.

## Foundation work before feature development

- [x] Start Docker Desktop and confirm the existing stack and tests still work: 40 tests / 138 assertions plus frontend and PHP checks passed on 2026-10-16.
- [x] Preserve the current migrations and authentication flows; all 5 existing migrations remain applied and authentication/settings tests pass. Future schema work must remain additive where practical.
- [x] Establish the application repository root covering `src/`, Docker files and `System_Plan/`. Connect to https://github.com/EnrieJohn31/BISU-Registrar.git; preserve starter Git metadata under ignored `.local-backups/2026-10-16-starter/`. Publication evidence is in the foundation validation report.
- [x] Configure root ignore rules for local secrets, runtime data, dependencies, exports and starter metadata backups; verify the staged file inventory before publication.
- [x] Review starter self-registration and account deletion before introducing retained institutional records; see [Part 03](03-accounts-permissions-and-audit.md). Done in slice 1 (C09): self-registration creates role-less accounts behind a config flag; self-deletion removed.
- [x] Introduce route authorization and shared page conventions before new modules. Done in [Part 03 slice 1](03-accounts-permissions-and-audit.md#how-every-request-is-authorized): policies with route `can`, audited admin refusals, allow-listed shared props.
- [x] Record baseline revision 84db53477d2fb22492b1d6789c072c7e7c87f746 and local/GitHub CI evidence in the index and foundation validation report.

## Acceptance criteria

- Existing starter routes and frontend route generation continue to work.
- Existing checks pass before a feature branch begins.
- Project documentation and Docker configuration are included in the chosen repository strategy.
- No production readiness is inferred from starter tests.

## Optimization note

Retain the existing Inertia application and component library. Measured 2026-10-17: with dependencies on the Windows bind mount, each request took 3–7 s because OPcache re-checks the ~800 vendor files a page loads. Moving `vendor/` and `node_modules/` to Docker volumes cut responses to 0.1–0.3 s and `check.ps1` from ~5 min to ~1 min. Moving the whole project into the WSL2 filesystem remains an option if Vite reloads or tests still feel slow.

## Evidence and change record

2026-10-15: inspected setup documentation, Composer/npm manifests, routes, model/migration inventory and Compose configuration. No application code changed in this planning task.

2026-10-16: established repository-wide setup/check scripts, root GitHub Actions/Dependabot configuration, contribution instructions and LAN-independent system fonts. Business features remain not started. See [foundation validation](../docs/FOUNDATION-VALIDATION.md).

2026-10-16: published the foundation and verified GitHub clean-checkout CI. [Run evidence](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38878033808). The remaining unchecked business-policy/authorization items belong to the upcoming feature work.

2026-10-18 (DEV-8): replaced the starter shared/sidebar logo and browser icons with local derivatives of the campus-hosted BISU seal. Preserved original source/provenance and added a Windows icon-regeneration script. Removed the exact untracked `src/nul` diagnostic file and ignored that path. Authentication/generated routes, office rules and database are unchanged. [Design, source and validation](../docs/DEV-8-BISU-BRANDING.md).
