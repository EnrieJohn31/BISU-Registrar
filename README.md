# BISU Registrar

Integrated Registrar-Cashier system planned for Bohol Island State University - Balilihan Campus. The development foundation, staff accounts and client-record slice are implemented; current local validation and remaining modules are tracked in [System_Plan/index.md](System_Plan/index.md).

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

Setup preserves an existing src/.env and application key, installs locked dependencies, starts MySQL/Redis/Mailpit, applies outstanding local migrations, builds assets and recreates the app/Vite/worker/scheduler against the rebuilt PHP image. It pauses those running containers before replacing dependencies and restores previously running services if setup fails. It refuses an APP_ENV other than local/testing. Use ./setup.ps1 -Rebuild to refresh base images.

Development URLs: app http://localhost:8085; Vite http://localhost:5178; test email inbox http://localhost:8026. All three bind to developer loopback. MySQL/Redis and Mailpit SMTP have no host ports. The local Compose database/user/password are bisu_registrar / laravel / laravel; root password is root. These development placeholders must be replaced in a future campus production configuration.

## Daily commands

Run from the repository root:

```powershell
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml ps
docker compose -f docker-compose.dev.yml logs --tail 100 app node worker scheduler mailpit
./check.ps1
docker compose -f docker-compose.dev.yml down
```

Stopping containers preserves the database volume. Never use down -v for routine shutdown because it deletes the database volume. When dependencies change, rerun setup rather than installing host-platform packages into Docker's node_modules.

PHP (`vendor/`) and frontend (`node_modules/`) dependencies live in Docker volumes, not in `src/`. Reading them through the Windows bind mount made every page take several seconds; from the volumes a page takes well under a second, and edits to your own code still apply immediately. Install or update packages inside the container, for example `docker compose -f docker-compose.dev.yml exec app composer require vendor/package` or `... exec app npm install package`; `./setup.ps1` reinstalls both. Any `src/vendor` or `src/node_modules` folder left on Windows is no longer used by the app (editors may still read it for autocompletion).

## Development jobs and email

The worker processes the existing database queue's `default` queue: three attempts, five-second backoff, 60-second timeout, with a 90-second reservation expiry. `schedule:work` runs in a separate container; no business tasks or retention/pruning jobs are enabled yet. After changing PHP job code or configuration, restart the long-running services:

```powershell
docker compose -f docker-compose.dev.yml restart worker scheduler
docker compose -f docker-compose.dev.yml exec app php artisan queue:failed
```

Fresh setups use Mailpit. Existing `.env` files are preserved, so use these local test settings in `src/.env` if upgrading a clone that used the log mailer:

```dotenv
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=90
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_URL=null
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

Then run `docker compose -f docker-compose.dev.yml exec app php artisan config:clear` and restart the worker/scheduler. `MAIL_URL` must be absent or `null`; an empty string overrides Laravel's SMTP transport. Test messages appear at http://localhost:8026 and are not relayed externally. The inbox is temporary, capped at 500 messages, and discarded when Mailpit stops; use synthetic content only. PHPUnit keeps its array mailer and synchronous queue. See [DEV-2 design and validation](docs/DEV-2-BACKGROUND-SERVICES.md).

## Source watching and demo data

Windows Docker source watching uses one-second polling (`VITE_USE_POLLING=true` in dev Compose). This prevents stale Vite modules when bind-mount file events are missed; ordinary host/Vite use keeps event-based watching. Retain the dependency/metadata watch exclusions to limit polling overhead.

Local test accounts (one per role, plus client, first-sign-in and deactivated examples). The seeder refuses to run in production and writes the sign-in details, including two-factor secrets, to `src/Credentials.md`, which git ignores:

```powershell
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=DevelopmentAccountsSeeder
```

On a campus server, MIS creates the first administrator with `php artisan bisu:create-admin` instead.

Fake client records for trying the Registrar's **Client records** screens (every name ends in "(Demo)" and every ID starts with "DEMO", so none can be mistaken for a real person). Run it after the accounts seeder; it is safe to run again and refuses to run in production:

```powershell
docker compose -f docker-compose.dev.yml exec app php artisan db:seed --class=DevelopmentClientsSeeder
```

Sign in as `processor@bisu.test` (can change records) or `intake@bisu.test` (view only); the password and other accounts are in `src/Credentials.md`. Try searching `DEMO-S-0001`, `reyes`, `zoë`, `00123456` and `villanueva`.

To add a component after a feature is scoped:

```powershell
docker compose -f docker-compose.dev.yml exec app npx shadcn-vue@latest add button
```

## Checks and collaboration

check.ps1 validates Compose/Composer, builds assets/Wayfinder routes, checks frontend formatting/lint and Vue/browser-test types, then runs PHP formatting, static analysis and the PHP test suite. It pauses Vite/worker/scheduler if running and restores their previous running state afterwards. PHP tests use an in-memory SQLite database; concurrency checks also need disposable MySQL tests. Browser execution is a separate command below. GitHub Actions has separate foundation and browser jobs; this local branch has not been published or run on GitHub.

## Saved browser regression suite (DEV-3)

Run from the repository root with Docker Desktop in Linux-container mode. No host PHP/Node, dev credentials or running development stack are required:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File ./browser-tests.ps1
```

The suite runs 34 named cases in four Chromium projects: desktop (1440 × 900) and phone emulation (390 × 844), each in light/dark mode. It uses a fixed, separate `bisu-registrar-browser` Docker project, a fresh SQLite database, random test credentials and real sign-in/TOTP flows. It copies only allowlisted source, never the development `.env`, credentials, runtime data, dependency volumes or Vite hot marker. It builds the app before browser execution and removes the isolated workspace/network afterwards, including on failure. It does not reset development records or expose a second app port.

Repeat or focus a run after the browser PHP image has been built:

```powershell
./browser-tests.ps1 -SkipBuild
./browser-tests.ps1 -SkipBuild -Project phone-dark -Grep B31
```

`-SkipBuild` skips only rebuilding the test PHP image; source, locked dependencies, assets and fixtures are still prepared fresh. First use downloads the matching pinned Playwright image and dependencies. Reports are in ignored `.browser-artifacts/report/index.html` and `.browser-artifacts/results.json`; failure screenshots/traces are in `test-results/`, and layout captures are in `screenshots/`. The JSON/HTML report identifies the current run; custom layout captures retain the latest capture for each project. CI retains only this synthetic evidence for seven days when the workflow is eventually published.

The four fixture-safety guards and TypeScript checks run in `./check.ps1`; actual browser tests run through `./browser-tests.ps1`. New parts should add independent cases and isolated fixtures. This suite is developer regression evidence; campus browser/device acceptance remains D20. [Design, case map and validation](docs/DEV-3-BROWSER-REGRESSION.md).

See [CONTRIBUTING.md](CONTRIBUTING.md) for branch/review conventions and [docs/FOUNDATION-VALIDATION.md](docs/FOUNDATION-VALIDATION.md) for evidence from repository preparation. Dependabot proposes weekly Composer, npm and GitHub Actions updates; updates require review and passing checks.

## Current scope and campus hosting

DEV-4's isolated PDF comparison can be rerun with `powershell -NoProfile -ExecutionPolicy Bypass -File ./pdf-spike.ps1`. It uses fictional stamped certificates and adds no application PDF package. Two sample packs and page previews are written to ignored `output/pdf/`; structural checks and visual review are both required. [Instructions](experiments/pdf-renderers/README.md) and [results/provisional recommendation](docs/DEV-4-PDF-RENDERERS.md).

Staff provisioning, office-scoped roles, session controls and an append-only audit viewer are implemented in Part 03. Part 04 slice 1 implements client records with synthetic data, complete paginated revision history and client reasons restricted to that history. Public self-registration grants no staff roles. Imports, identity linkage, requests, office ticket queues, payments and documents remain unimplemented. DEV-2 adds a development database-job worker, scheduler and captured-email inbox; campus email delivery remains gated by D11. Current authorization covers work that can proceed while offices answer (C11), with existing per-task approval gates preserved. See the [Part 04 QA report](docs/PART-04-QA-2026-10-18.md) and [saved browser evidence](docs/DEV-3-BROWSER-REGRESSION.md).

The intended operational host is inside BISU's campus LAN/Wi-Fi. This Docker setup binds to developer loopback and is not campus deployment. Internal HTTPS/DNS, production web serving, access controls, backups and office approval are planned in [Part 14](System_Plan/14-campus-deployment-and-operations.md).

The shared/sidebar mark and browser icons use local derivatives of the BISU seal from the official campus website. Source provenance, regeneration instructions and validation are in [DEV-8 branding](docs/DEV-8-BISU-BRANDING.md). The app makes no external request to load these assets.

## Commit attribution

AI author/co-author credits are disabled for this project. Keep actual human authorship. Setup activates the local commit/push guards; existing checkouts can run `./install-git-hooks.ps1` (PowerShell) or `git config --local core.hooksPath .githooks` (Git Bash/Linux/macOS). Existing custom hooks are preserved for manual integration. The required Foundation checks also reject AI attribution, including if local hooks were bypassed or were not installed.

Claude Code automatic commit/PR attribution is disabled for this project in `.claude/settings.json` with empty `attribution.commit` and `attribution.pr` strings. The Git hooks and required CI remain independent backup checks. Start a new Claude Code session in this checkout to ensure the project settings are loaded; other tools remain subject to the same Git/CI policy.

The guard recognizes AI credit trailers and primary authors; ordinary mentions of tools in a commit description and real human co-authors remain allowed. Run `sh .githooks/test-attribution.sh` to verify its behavior. Only repository owners can deliberately override or change these controls; automated work must never do that.

## Source attribution

Created from the MIT-licensed [official Laravel Vue starter kit](https://github.com/laravel/vue-starter-kit). Original foundation setup evidence remains in [SETUP-VALIDATION.md](SETUP-VALIDATION.md).
