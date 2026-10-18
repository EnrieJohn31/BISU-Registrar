# DEV-3 — Saved browser regression suite

[Part 13](../System_Plan/13-testing-and-campus-acceptance.md) | [Part 04](../System_Plan/04-client-records-and-imports.md) | [Waiting work](../System_Plan/97-work-while-waiting.md)

## Scope and design — 2026-10-18

The owner authorized DEV-3 as the next eligible waiting-work item. C11 remains in force: synthetic fixtures and existing rules only; no imports, linking, office workflows or policy changes.

Use Playwright Test with a matching pinned official browser image. Save 34 named cases covering the original workflow and the follow-up full-history/audit regressions. Run Chromium at desktop/phone widths, in light/dark themes. This is browser emulation, not the D20 campus-device acceptance list.

An independent Compose project copies only allowlisted application source into a disposable workspace, installs locked dependencies, builds assets/Wayfinder and seeds an isolated SQLite database. The source is mounted read-only; development `.env`, credentials, runtime storage, database files and the Vite hot marker are excluded. No development volume/network/database or worker is reused. Random test passwords, encryption keys, TOTP secrets and browser sessions stay in the disposable workspace. Real sign-in and two-factor flows remain enabled.

The root runner must always clean up only its fixed browser-test project, preserving the test exit code. Test reports, traces and screenshots go to ignored `.browser-artifacts/`; retained evidence contains synthetic test data only. CI uses the same runner. Changes to the saved TypeScript tests receive formatting/lint/type checks as well as actual browser execution.

## Validation record

Run from the repository root:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File ./browser-tests.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File ./check.ps1
```

The browser suite has passed **136 checks: 34 cases × four projects, with zero retries**. The full-history case checks 50 newest revisions, the remaining creation revision on page 2, disabled final-page Next and return to the newest history. The general audit case verifies that a reason containing a demo name/full ID remains in restricted history and is absent from the administrator's audit response and drawer.

After screenshot review, B33 gained explicit detail-heading/full-ID readiness assertions; B21/B27 also gained reason/full-ID assertions. The final focused rerun of those three cases passed **12/12** across all four projects. Its refreshed detail captures show the intended page. Frontend formatting/lint and browser TypeScript passed again after these final assertions. This session preserved the preceding complete matrix as `.browser-artifacts/full-matrix-results.json` and `full-matrix-report/index.html`; the usual `results.json`/`report/index.html` now describe the final focused run.

Screenshot review covers list, detail and newest/oldest history at desktop and phone widths in both themes. Phone tables keep horizontal scrolling inside their cards, and history text/paging controls wrap without page-level overflow. Screenshots are viewport captures, with history captures deliberately scrolled to the history section. They are developer layout evidence, not approved office templates or campus-device acceptance.

The development `.env` SHA-256 and read-only database aggregates (user/profile/revision/audit counts plus profile-version sum) matched before and after the isolated full runs. Test-project cleanup removed its containers, workspace and network. Failure cleanup was also observed during initial harness troubleshooting. No developer records were reset or edited by the suite.

`./check.ps1` passed: **167 PHP tests / 944 assertions**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue TypeScript, browser TypeScript, Pint and Larastan level 7. It restored the running Vite/worker/scheduler afterwards. The added fixture guards account for four tests / eight assertions; existing business tests remain intact.

Both Compose configurations, the PowerShell runner, both shell scripts and workflow YAML passed syntax/configuration checks. `git diff --check` passed, and 143 relative Markdown links in ten changed documents resolved. All seven development services were running at final inspection; the isolated browser project's containers, network and volume were absent.

## Findings and limits

- No new client-record application defect was found in this slice. Existing history paging and restricted audit reasons now have repeatable browser coverage. No application route/controller, permission or schema changed.
- Initial harness checks exposed two test issues: a whitespace-sensitive pagination comparison, replaced with stable record-link identity, and a detail screenshot captured before Inertia navigation completed, now guarded by detail-heading/full-ID assertions. Fixture configuration uses Laravel config rather than reading environment variables in the seeder, so cached configuration remains valid.
- The temporary original CDP script was unavailable. This suite reconstructs its documented coverage; it is not a byte-for-byte conversion or another run of the original Edge script.
- This suite uses Chromium and SQLite. It does not rerun MySQL race probes, validate physical Edge/Safari/Firefox installations, perform screen-reader acceptance or approve D20's campus browser list. Existing MySQL/race evidence remains dated in the Part 04 reports.
- The isolated build uses the existing default `BISU_REGISTRATION_ENABLED=true`; no policy is changed. An initial attempt with registration disabled exposed an existing build limitation: `Register.vue` imports a generated registration route which is absent in that configuration. A separately scoped technical fix and disabled-registration build check are needed before using that setting. Registration itself is outside these client-record cases.
- CI is wired but has not run on GitHub because this branch is local. First use requires dependency/image downloads. Matching Playwright package/image versions must be updated together. Run one local browser runner at a time because the disposable project name is fixed.

## Connections and next use

`docker-compose.browser.yml`, `browser-tests.ps1` and `scripts/browser/` own the disposable runtime. `BrowserTestSeeder` uses existing client actions and role factories; its four negative guard cases reject a non-testing environment, absent opt-in, another driver or another database path before any data access. It also refuses a nonempty database rather than resetting records. `config/browser.php` defaults its fixture opt-in to false.

`src/tests/Browser/`, `playwright.config.ts` and `tsconfig.browser.json` hold the suite. Locked `@playwright/test` 1.64.1 matches the official `v1.64.1-noble` browser image. `check.ps1` and the foundation CI job include browser TypeScript and PHP fixture guards; the separate CI browser job runs the root runner and retains synthetic artifacts for seven days. No campus application port is exposed.

Evidence stays in ignored `.browser-artifacts/`: `report/index.html`, `results.json`, failure-only traces/screenshots under `test-results/`, and named layout captures under `screenshots/`. The report identifies the current run; layout captures retain the latest file per project. Example focused run: `./browser-tests.ps1 -SkipBuild -Project phone-dark -Grep B31`. `-SkipBuild` skips only the PHP-image build and still prepares fresh source/dependencies/assets/fixtures.

For later parts, add independent cases and synthetic fixtures using the same real authorization flows. Preserve the fixed internal URL and isolated database guards. If repeat installation becomes a bottleneck, consider dedicated test-only package-download caches; do not share writable development dependency/runtime volumes. D20 can add named browser/device projects after MIS supplies the inventory.

## References

- [Playwright Docker and matching versions](https://playwright.dev/docs/docker)
- [Playwright test isolation](https://playwright.dev/docs/browser-contexts)
- [Playwright fixtures](https://playwright.dev/docs/test-fixtures)
- [Playwright projects](https://playwright.dev/docs/test-projects)

## Saved cases

The original temporary CDP script was not present in the repository. These cases reconstruct its documented coverage and add the full-history/audit follow-ups and keyboard checks. Every case runs once per project; there are no automatic retries.

| ID | Case |
|---|---|
| B01 | processor dashboard opens client records |
| B02 | list masks full IDs and shows record counts |
| B03 | shared names remain separate search matches |
| B04 | ID prefix search finds alumni identifiers |
| B05 | leading-zero ID search preserves text |
| B06 | Unicode names remain searchable |
| B07 | empty search result is explained |
| B08 | category filter includes and excludes the same record |
| B09 | verification filter selects the review case |
| B10 | detail exposes the authorized full ID and provenance |
| B11 | one person may have student and alumni IDs |
| B12 | retired IDs remain visible in history |
| B13 | list pagination reaches and returns from the next page |
| B14 | create records the first ID and creation history |
| B15 | case-insensitive duplicate IDs keep form values |
| B16 | shared new names never merge profiles |
| B17 | correction retains old and new names |
| B18 | no-op correction is refused |
| B19 | verification records actor and source reason |
| B20 | correcting a verified client warns and returns to review |
| B21 | flagging preserves the person and reason |
| B22 | add an alumni ID to an existing person |
| B23 | an existing alumni ID cannot be reassigned |
| B24 | retiring an old ID preserves it as history |
| B25 | the last current ID cannot be retired |
| B26 | a stale correction refreshes instead of overwriting |
| B27 | Intake sees full details without change buttons |
| B28 | Intake create URL is denied by the server |
| B29 | client reasons remain outside the general audit viewer |
| B30 | Cashier has no client navigation and cannot open records |
| B31 | full history pages reach creation and return to the newest revision |
| B32 | long names stay inside the list layout |
| B33 | detail layout has no page-level horizontal overflow |
| B34 | correction dialog cancels with Escape and restores keyboard focus |
