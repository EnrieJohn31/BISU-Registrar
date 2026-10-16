# Contributing to BISU Registrar

## Working agreement

Read [the system index](System_Plan/index.md) and the affected module plan. Keep code usable, verify connected flows, document changes and report tests/remaining issues. See [AGENTS.md](AGENTS.md) for development instructions.

## Branches and review

- main contains the reviewed foundation and future accepted changes.
- Use feature/<short-topic>, fix/<short-topic> or chore/<short-topic> branches for subsequent work.
- Link the relevant plan part and decisions in the pull request. Keep unrelated work in separate changes.
- Run ./check.ps1 and inspect the diff before requesting review. GitHub's Foundation checks job must pass before merging.
- Never force-push main or replace an existing repository history. Repository-level branch rules depend on GitHub settings and are recorded separately from this working convention.

## Dependencies and schema

- Commit src/composer.lock and src/package-lock.json together with intentional manifest changes.
- Use composer install and npm ci for reproducible setup; use updates only for intentional dependency work.
- Add explicit migrations; explain backfills, rollback/forward-fix plans and effects on connected modules.
- Keep credentials, real client records, document uploads, database dumps and runtime output out of commits and CI logs.

## Documentation and deployment

Update the relevant plan, tracker and change log when implementation or decisions change. Report what changed, why, validation results and useful optimization suggestions. Record actual staging/production releases in Part 14; a push to GitHub is source publication, not campus deployment.
