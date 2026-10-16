# Part 00 — Current application baseline

[Back to index](index.md) | [Next: requirements and workflow](01-requirements-and-workflow.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft, based on inspected files |
| Implementation | Foundation implemented; business features not started |
| Validation | Local build/lint/types/static analysis and 40 tests passed on 2026-10-16; GitHub CI pending |
| Deployment | Local Docker configured; staging and production not recorded |
| Owner | Project developer / MIS, assignment pending |
| Dependencies | None |
| Updated | 2026-10-16 |

## Purpose

Establish the starting point so future work extends the existing application and preserves its working connections. This plan set describes future development; creating a planning file does not implement its features.

## What exists

| Area | Evidence / current state |
|---|---|
| Backend | Laravel 13; setup lockfile resolved framework 13.36.0; PHP 8.4 in Docker |
| Frontend | Vue 3, Inertia 3, TypeScript, Tailwind CSS 4, shadcn-vue, Vite |
| Authentication | Starter login, registration, email verification, password recovery, account settings, two-factor/passkey support |
| Screens | Starter welcome, dashboard placeholders, authentication and settings |
| Database | MySQL 8.4; users, sessions, cache, jobs, passkeys and two-factor migrations |
| Docker | app, node/Vite, MySQL and Redis services in `../docker-compose.dev.yml` |
| URLs | Configured local app: `http://localhost:8085`; Vite: `http://localhost:5178` |
| Application location | `../src/`; Docker files and setup script live at project root |
| Existing evidence | [Setup validation](../SETUP-VALIDATION.md), [setup instructions](../README.md) |

The previous setup report records 40 passing tests / 138 assertions, passing frontend and PHP checks, successful migrations, and successful HTTP checks. These are historical setup results, not tests of registrar features.

On 2026-10-15 the planning inspection could not contact the Docker Desktop Linux engine. That inspection did not establish service availability. On 2026-10-16 Docker Desktop was started for repository preparation; updated results are recorded in [foundation validation](../docs/FOUNDATION-VALIDATION.md). A local Docker configuration or a compiled asset build does not establish a campus production deployment.

## Features that still need implementation

- Client identity linkage and verified student/alumni records.
- Office roles, permission enforcement and business audit events.
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
- [ ] Review starter self-registration and account deletion before introducing retained institutional records; see [Part 03](03-accounts-permissions-and-audit.md).
- [ ] Introduce route authorization and shared page conventions before new modules.
- [ ] Record a baseline commit/revision and actual test evidence in the index.

## Acceptance criteria

- Existing starter routes and frontend route generation continue to work.
- Existing checks pass before a feature branch begins.
- Project documentation and Docker configuration are included in the chosen repository strategy.
- No production readiness is inferred from starter tests.

## Optimization note

Retain the existing Inertia application and component library. Investigate WSL2 storage or container dependency volumes only if measured Windows bind-mount performance affects development.

## Evidence and change record

2026-10-15: inspected setup documentation, Composer/npm manifests, routes, model/migration inventory and Compose configuration. No application code changed in this planning task.

2026-10-16: established repository-wide setup/check scripts, root GitHub Actions/Dependabot configuration, contribution instructions and LAN-independent system fonts. Business features remain not started. See [foundation validation](../docs/FOUNDATION-VALIDATION.md).
