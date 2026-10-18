# DEV-2 — Development worker, scheduler and test inbox

[Part 14](../System_Plan/14-campus-deployment-and-operations.md) | [Waiting-work plan](../System_Plan/97-work-while-waiting.md) | [Handoff](../System_Plan/handoff.md)

## Scope and design — 2026-10-18

The project owner authorized the next recommendation, DEV-2, on 2026-10-18 and limited ongoing work to items that can proceed while office answers are pending. This slice supplies development infrastructure only. Imports, client linking, office workflows, fees, notification policies, retention schedules and campus deployment keep their existing decision gates.

- Reuse the existing PHP development image, source mount and dependency volumes for app, Vite, worker and scheduler. No new PHP/npm package or schema change.
- Keep the durable **database** queue already configured. One worker processes `default`, with three attempts, five-second retry backoff and a 60-second timeout. Its 90-second reservation expiry is longer than the timeout; shutdown allows 75 seconds for an active job. These are development defaults, not campus performance targets.
- Run `php artisan schedule:work` in its own container. No business tasks, pruning or retention rules are added merely to populate the scheduler.
- Use the official `axllent/mailpit:v1.31.6` image. Only the inbox UI/API is published, at `127.0.0.1:8026`; SMTP stays inside Docker at `mailpit:1025`. No relay or forwarding is configured. Captured mail is temporary and bounded to 500 messages; it is not an office record or backup. Automatic version checks are disabled.
- Fresh `.env.example` files use Mailpit SMTP. Preserve existing `.env` files and document the explicit mail settings for existing clones; apply those settings to this approved local development session without touching unrelated values or credentials. PHPUnit retains its array mailer/synchronous queue settings.
- Pause running app/Vite/worker/scheduler during dependency replacement in setup; checks pause Vite/worker/scheduler during asset generation and tests. Restore only previously running services on completion/failure. Successful setup recreates PHP runtime containers against the image just built, while retaining MySQL/Redis/Mailpit. Long-running workers must be restarted after PHP/config changes.

## Validation plan

Validate Compose and script syntax, start the services, verify a synthetic queued email is handled by the dedicated worker and appears in Mailpit, exercise retries/failed-job visibility with disposable probes, observe the scheduler tick with no business tasks, and run `./check.ps1`. Check service restoration and application/authentication response. Keep probes and runtime records out of Git; record final evidence here.

## Implementation and evidence

Local branch: `feat/dev-background-services-2026-10-18`, based on the Part 04 QA commit `896e59e`. No push, PR, merge or campus deployment. Files: root Compose/setup/check scripts and `src/.env.example`; documentation updates cover Parts 04/11/14, the decision register, index, waiting-work tracker, README and handoff. Authentication, permissions, route generation, application workflows and database schema are unchanged.

| Check | Result |
|---|---|
| Compose configuration and PowerShell parser | Passed |
| Full setup | Passed: built the shared PHP image once, installed locked dependencies, applied no new migration, built assets and started all seven services |
| Existing configuration preservation | Setup preserved the local `.env` byte for byte, including the existing application key. Only the explicitly selected Mailpit/reservation settings were edited separately for this session |
| Dedicated worker and SMTP | Synthetic queued closure ran in a different container from the producer and delivered exactly one `(Demo)` email to `runtime-check@bisu.test`, captured by Mailpit's API |
| Retry recovery | Deliberate first-attempt failure recovered on attempt 2 with the five-second backoff |
| Exhausted attempts | Separate always-failing demo job stopped after attempt 3; its failed-job entry was verified and removed by UUID. Pending/failed queues returned to zero |
| Scheduler | Container logged its minute tick and “No scheduled commands are ready to run”; `schedule:list` confirmed no business schedule |
| Failed-check restoration | Injected a harmless Composer-validation failure after services paused. Running Vite/worker were restored; a deliberately stopped scheduler remained stopped. Scheduler was explicitly restored after the probe |
| Final foundation checks | Passed: **163 tests / 936 assertions**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue TypeScript, Pint and Larastan level 7. Running Vite/worker/scheduler were restored afterwards |
| Final startup/smoke | Setup retest passed; all four PHP containers matched the rebuilt image, all seven services were running, app login/inbox returned HTTP 200 and the captured demo email was retained. PowerShell/Compose/whitespace checks passed; 135 local links in ten changed documents resolved |

The initial live email probe exposed `MAIL_URL=` overriding Laravel's SMTP transport. Replaced it with `MAIL_URL=null`, restarted the background processes and reran delivery/retry/failure probes successfully. Only synthetic failed-job UUIDs were removed; no client/account records were edited. A captured demo email is left in the temporary inbox for inspection. Probe scripts/markers are excluded from Git and removed after validation.

Final image review found that Docker retained old image references on stopped runtime containers after a rebuild. Setup now explicitly recreates app/Vite/worker/scheduler against the image just built, preserving the database/cache/inbox services; its complete startup was rerun to verify this path.

Mailpit image pulled: `axllent/mailpit:v1.31.6`, digest `sha256:23fe39e81bde596a918000b3135c0f85dc8bb4c9ea6be274b051143f3e300387`. The UI/API binds to developer loopback; SMTP, MySQL and Redis have no host ports. No campus mail relay or Internet delivery was configured.

## Boundaries and next work

These services are development prerequisites. The worker is not the office ticket queue (Part 07), and capturing mail does not implement Part 11 or resolve D11. D04/D09 still gate imports; D03 gates linking; D05/D07 gate real services/fees/finance; D08 gates real templates and signatures. No cleanup/retention schedule was invented.

Restart worker/scheduler after PHP or configuration changes. Future long jobs need measured timeouts, idempotency and workload-specific queue choices before implementation; keep timeout shorter than reservation expiry. Run only one development scheduler. Next waiting-work task: DEV-3 saved browser regression tests, including the pending Part 04 pagination visual check.

## References

- [Laravel queue workers, timeouts and retries](https://laravel.com/docs/13.x/queues)
- [Laravel local scheduler](https://laravel.com/docs/13.x/scheduling#running-the-scheduler-locally)
- [Official Mailpit Docker images](https://mailpit.axllent.org/docs/install/docker/)
- [Mailpit runtime options](https://mailpit.axllent.org/docs/configuration/runtime-options/)
- [Pinned Mailpit release](https://github.com/axllent/mailpit/releases/tag/v1.31.6)
