# Repository foundation validation - 2026-10-16

Repository: https://github.com/EnrieJohn31/BISU-Registrar

## Scope

Prepare the existing foundation for feature work: root Git repository, complete source/planning/Docker tracking, safe exclusions, reproducible local setup/checks, root CI/Dependabot, contribution instructions and locally rendered system fonts. Registrar/Cashier business modules are not added.

The empty user-supplied remote was inspected before initialization. Existing starter Git and GitHub metadata are preserved under ignored .local-backups/2026-10-16-starter/. The original project path remains the application checkout.

## Validation status

- Docker Desktop started; engine version 29.8.2.
- Docker image build and Compose configuration: passed.
- setup.ps1 completed successfully using locked dependencies; all 5 existing migrations remain applied.
- composer validate --strict: passed. Refreshed stale lock metadata without changing any package version.
- Frontend production build and Wayfinder generation: passed.
- Frontend formatting: 69 files passed; lint: 62 files passed with no warnings/errors.
- Vue TypeScript: passed.
- PHP Pint: 54 files passed; PHPStan: no errors.
- PHPUnit: 40 tests passed, 138 assertions.
- Home, login, registration and Vite client endpoints: HTTP 200; no external font references in those responses.
- MySQL/Redis healthy; Redis returned PONG.
- PowerShell setup/check scripts: syntax passed; check.ps1 restored Vite after a caught README-formatting failure, then the corrected formatting/types/PHP stages passed. Asset build had already passed.
- Existing src/.env and application key: unchanged, verified by hash comparison before and after setup.
- Staged inventory: 330 files; no forbidden secret/runtime paths, submodule entries or credential-pattern matches. Plan links: 151 checked, 0 broken.
- Initial commit/push and GitHub Actions result: awaiting publication; results will be recorded after verification.
- Campus staging/production: not deployed.

## Notes

CI uses the same PHP 8.4/Node 24 Docker runtime and lockfiles as development. Tests use in-memory SQLite; future payment/queue/release concurrency tests must also use a disposable MySQL database. Starter self-registration/profile deletion policy is still an open business-feature decision.
## Resolved during preparation

The starter installer had changed Composer metadata after creating the original lockfile. A lock-only refresh resolved strict validation; every locked package version was compared and preserved. The new app README required formatter normalization, which was applied before publication. Windows PowerShell script execution uses a process-only override documented in README; the machine-wide policy was not changed.

## Optimization note

Measured local builds spent roughly 60-70 seconds primarily in Tailwind and route-generation plugin work on Windows bind mounts. Keep the simple development setup initially; consider WSL2 filesystem storage if repeated builds slow feature work. Do not add infrastructure or caches without measuring the benefit.