# BISU Registrar

Integrated Registrar-Cashier system planned for Bohol Island State University - Balilihan Campus. The repository currently contains the clean development foundation; registrar/cashier business features are planned in [System_Plan/index.md](System_Plan/index.md).

[![Foundation checks](https://github.com/EnrieJohn31/BISU-Registrar/actions/workflows/checks.yml/badge.svg)](https://github.com/EnrieJohn31/BISU-Registrar/actions/workflows/checks.yml)

## Stack and repository layout

Laravel 13, PHP 8.4, Vue 3, Inertia 3, TypeScript, Tailwind CSS 4, shadcn-vue, MySQL 8.4, Redis 7, Composer 2 and Node 24. Both application dependency lockfiles are committed.

```text
BISU-Registrar/
  .github/                 GitHub Actions, dependency updates, PR template
  docker/                  Development PHP configuration
  docs/                    Development and validation notes
  src/                     Laravel application and frontend
  System_Plan/             Features, decisions and deployment tracker
  Dockerfile.dev
  docker-compose.dev.yml
  setup.ps1                Initial/repeat local setup
  check.ps1                Complete local foundation checks
```

The Git repository root is this directory, not src/. The original starter Git metadata is preserved locally under ignored .local-backups/. No starter metadata, local secrets, installed dependencies or runtime data belongs in commits.

## Prerequisites

- Git and Docker Desktop configured for Linux containers.
- PowerShell on Windows. PHP, Composer and Node run inside Docker; host installations are optional.
- Internet access for initial image/package downloads and dependency updates. The built application uses system fonts and does not request external font stylesheets.

## Fresh checkout and setup

```powershell
git clone https://github.com/EnrieJohn31/BISU-Registrar.git
cd BISU-Registrar
./setup.ps1
./check.ps1
```

If Windows blocks local PowerShell scripts, use a process-only override; this does not change the machine's execution policy:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\setup.ps1
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\check.ps1
```

Setup preserves an existing src/.env and application key, installs locked dependencies, starts MySQL/Redis, applies outstanding local migrations, builds assets and starts the application/Vite. It stops a running Vite service while replacing dependencies and restores it afterward. It refuses an APP_ENV other than local/testing. Use ./setup.ps1 -Rebuild to refresh base images.

Development URLs: app http://localhost:8085; Vite http://localhost:5178. MySQL/Redis have no host ports. The local Compose database/user/password are bisu_registrar / laravel / laravel; root password is root. These development placeholders must be replaced in a future campus production configuration.

## Daily commands

Run from the repository root:

```powershell
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml ps
docker compose -f docker-compose.dev.yml logs --tail 100 app node
./check.ps1
docker compose -f docker-compose.dev.yml down
```

Stopping containers preserves the database volume. Never use down -v for routine shutdown because it deletes the database volume. When dependencies change, rerun setup rather than installing host-platform packages into Docker's node_modules.

PHP (`vendor/`) and frontend (`node_modules/`) dependencies live in Docker volumes, not in `src/`. Reading them through the Windows bind mount made every page take several seconds; from the volumes a page takes well under a second, and edits to your own code still apply immediately. Install or update packages inside the container, for example `docker compose -f docker-compose.dev.yml exec app composer require vendor/package` or `... exec app npm install package`; `./setup.ps1` reinstalls both. Any `src/vendor` or `src/node_modules` folder left on Windows is no longer used by the app (editors may still read it for autocompletion).

Local test accounts (one per role, plus client, first-sign-in and deactivated examples). The seeder refuses to run in production and writes the sign-in details, including two-factor secrets, to `src/Credentials.md`, which git ignores:

```powershell
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=DevelopmentAccountsSeeder
```

On a campus server, MIS creates the first administrator with `php artisan bisu:create-admin` instead.

To add a component after a feature is scoped:

```powershell
docker compose -f docker-compose.dev.yml exec app npx shadcn-vue@latest add button
```

## Checks and collaboration

check.ps1 validates Compose/Composer, builds assets/Wayfinder routes, checks frontend formatting/lint/types, then runs PHP formatting, static analysis and the test suite. Tests currently use an in-memory SQLite database; later concurrency work also needs disposable MySQL integration tests. GitHub Actions runs the same application checks in the same Docker runtime from a clean checkout.

See [CONTRIBUTING.md](CONTRIBUTING.md) for branch/review conventions and [docs/FOUNDATION-VALIDATION.md](docs/FOUNDATION-VALIDATION.md) for evidence from repository preparation. Dependabot proposes weekly Composer, npm and GitHub Actions updates; updates require review and passing checks.

## Current scope and campus hosting

Starter authentication/settings and dashboard placeholders exist. Public self-registration is still the starter behavior; office roles, identity linkage, records/imports, requests, queues, payment verification and documents are not implemented. Mail uses the log driver. Database-backed sessions/cache/jobs are configured; future asynchronous work needs a worker and scheduler.

The intended operational host is inside BISU's campus LAN/Wi-Fi. This Docker setup binds to developer loopback and is not campus deployment. Internal HTTPS/DNS, production web serving, access controls, backups and office approval are planned in [Part 14](System_Plan/14-campus-deployment-and-operations.md).

## Source attribution

Created from the MIT-licensed [official Laravel Vue starter kit](https://github.com/laravel/vue-starter-kit). Original foundation setup evidence remains in [SETUP-VALIDATION.md](SETUP-VALIDATION.md).


Windows Docker source watching uses one-second polling (VITE_USE_POLLING=true in dev Compose). This prevents stale Vite modules when bind-mount file events are missed; ordinary host/Vite use keeps event-based watching. Retain the dependency/metadata watch exclusions to limit polling overhead.
