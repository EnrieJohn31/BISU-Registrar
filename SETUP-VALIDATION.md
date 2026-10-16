# Setup validation — 2026-10-15

## Completed

- Docker development image builds successfully (PHP 8.4 / Node 24 / Composer 2).
- Docker Compose configuration validates.
- MySQL and Redis containers are healthy.
- Composer dependencies installed; Laravel framework resolved to 13.36.0.
- npm dependencies installed; both dependency lockfiles generated.
- Laravel starter feature installation and production asset build completed successfully.
- Application key generated without recording it in this report.
- All 5 database migrations passed against the Docker MySQL database.
- PHPUnit: 40 tests passed, 138 assertions (SQLite in-memory testing database).
- PHP Pint: 54 files passed.
- PHPStan static analysis: passed, no errors with the 512 MB development limit.
- Vue TypeScript: passed.
- Frontend formatting: 69 files passed.
- Frontend lint: 62 files passed, no warnings or errors.
- Home, login, registration, Vite client and app TypeScript endpoints: HTTP 200.
- Home response contains BISU Registrar.
- Redis connectivity: PONG.
- PowerShell setup script: syntax parser passed.

## Resolved during setup

- The first test run began before the frontend manifest existed. Rerunning after
  the asset build passed all 40 tests.
- PHPStan exhausted PHP's default 128 MB memory limit. Added docker/php.ini with
  a 512 MB development limit and rebuilt/recreated the application services.

## Notes

The official starter feature installer removed scaffolding markers and its own
installer command as designed. App code, authentication routes, frontend route
generation, and components remain connected.

The starter template's Git metadata is retained in src/. Docker configuration
and this documentation live one directory above src/; include these when
establishing the application's own repository.

The development font plugin reports that optional fontaine is not installed.
Font rendering/build works; install it only if optimized font fallbacks are needed.
