# Handoff

[Back to index](index.md) | [Waiting-work plan](97-work-while-waiting.md) | [Decision register](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

Rewrite this file after each completed slice; dated history belongs in the change log.

## Current handoff — 2026-10-18

**Stage:** M1 development. Part 03 slice 1 is merged. Part 04 slice 1, its QA fixes (`896e59e`) and DEV-2 development infrastructure remain local. Current branch: `feat/dev-background-services-2026-10-18`, based on `fix/part-04-qa-2026-10-18`. Campus staging/production are not deployed.

**Current scope (C11): work only on items that can proceed while offices answer.** The owner authorized DEV-2 in this session. Preserve later slices' design/approval gates; an incoming office answer does not automatically authorize a gated business feature. Keep synthetic data labelled. Imports, linking, office workflows and real fees/IDs/templates remain outside current work.

**Git policy:** local commits only; no push, PR or GitHub merge without an explicit owner request. Preserve actual human authorship and the active commit/push guards. Historical attribution details: [record](../docs/CONTRIBUTOR-ATTRIBUTION-CLEANUP.md).

### Just completed — DEV-2

- Added one database/default queue worker, a local scheduler and Mailpit. App/Vite/worker/scheduler reuse one PHP image and the existing source/dependency volumes. No application feature, package or migration was added.
- Worker defaults: 3 attempts, 5-second backoff, 60-second timeout, 90-second reservation expiry, 75-second shutdown grace and hourly process recycling. These are dev defaults; future long jobs need their own measured settings/idempotency design.
- Scheduler runs `schedule:work`; no business, pruning or retention tasks were invented.
- Mailpit captures development SMTP at `mailpit:1025`; inbox: http://localhost:8026. UI/API is loopback-only; SMTP has no host port. No external relay/forwarding. Inbox data is temporary, capped at 500 demo messages and discarded when Mailpit stops.
- Setup safely pauses app/Vite/worker/scheduler during dependency replacement and recreates them against the newly built image afterwards. Checks pause and restore running Vite/worker/scheduler. Successful setup starts all seven dev services; failure cleanup preserves prior running/stopped service states.
- Fresh `.env.example` uses SMTP/Mailpit. Setup preserves existing `.env` files; the README lists upgrade settings. This session separately changed only local test-mail/reservation settings, preserving unrelated values and the application key. `MAIL_URL` must be absent or `null`, not an empty string.

Design, connected files, commands and detailed evidence: [DEV-2 report](../docs/DEV-2-BACKGROUND-SERVICES.md).

### Validation and runtime

- `./setup.ps1` passed end to end; the existing local environment file remained byte-for-byte unchanged during setup. No new migration was needed.
- Live synthetic queue probes passed: dedicated worker confirmed, email captured exactly once after recovery on attempt 2, a separate job failed after attempt 3 and appeared in failed-job storage. Only the synthetic failed UUIDs were removed; pending and failed queues returned to zero. One captured `(Demo)` email remains in the temporary inbox for inspection.
- Scheduler minute tick observed; no business schedules defined. Injected check failure restored running Vite/worker while an intentionally stopped scheduler stayed stopped; the complete stack was then restored.
- Final `./check.ps1`: **163 tests / 936 assertions passed**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue TypeScript, Pint and Larastan level 7. All running background services were restored afterwards. Temporary probes/markers are removed and excluded from Git.
- URLs: app http://localhost:8085; Vite http://localhost:5178; captured test inbox http://localhost:8026. This is development Docker, not campus deployment.

### Part 04 state and remaining QA

Client records retain C10's approved permissions and demo rules. Full history is now paginated; free-text client reasons stay in restricted revision history and general audit events reference the revision. Existing demo profiles/accounts were not reset or edited by DEV-2.

Part 04 QA: [2026-10-18 report](../docs/PART-04-QA-2026-10-18.md); its disposable MySQL run passed 45 tests / 394 assertions. The original 34-check Edge walkthrough/race evidence is dated 2026-10-17. New pagination visual confirmation is still pending because no browser session was exposed during that QA run. DEV-2 does not claim another client-screen walkthrough or office acceptance.

Demo access remains `processor@bisu.test` (changes) and `intake@bisu.test` (view only); credentials stay in ignored `src/Credentials.md`. Use README seeders only when fixtures are missing, without resetting existing data merely to repeat QA.

### Decisions and blockers

| Decision | Owner | Still needed |
|---|---|---|
| D03 / D04 / D09 | Registrar + MIS + DPO | Linking proof/recovery, official ID formats, approved import source/reviewer/correction authority, permitted fields, privacy/retention/access auditing |
| D05 / D07 | Registrar + Cashier | Complete charter, approved services/requirements/fees and financial rules |
| D08 | Registrar | Real templates, signatures, numbering and release rules |
| D11 / D13 / D20 | MIS + offices | Campus mail/assisted delivery, workload and operational targets, supported browsers/devices |

A running development worker resolves only a technical prerequisite. It does not unblock imports' source/privacy/reviewer gates or decide a notification/retention policy.

### Next work, within the waiting-work list

1. DEV-3: save the 34-check browser walkthrough as a repeatable regression suite, including the pending full-history paging check. Keep synthetic fixtures isolated from developer/office records.
2. Follow the agreed order for supplied BISU branding, the synthetic PDF-renderer comparison and Part 05 design. Part 05 implementation still requires approval of its design; later build slices need their own scoped task.
3. Team/office preparation remains in [97](97-work-while-waiting.md): assign names/dates, thesis chapters/diagrams, office demo/device checks, privacy draft and permission-based anonymous timing baseline. No messages or bookings were sent by this slice.
4. Record incoming office answers in the decision register; continue within C11 until a separate feature task is authorized. Publishing local branches also requires an explicit request.

### Watch-outs and useful optimizations

- Restart worker/scheduler after PHP job or configuration changes: `docker compose -f docker-compose.dev.yml restart worker scheduler`. They are long-running processes.
- Keep timeout below reservation expiry and design future jobs for retries. Avoid adding cleanup schedules until retention is approved. Run only one development scheduler.
- Keep authentication, generated routes and PHP/TypeScript permissions connected. Every client write goes through `ClientProfileRecorder`; reasons and full client details stay out of general audit.
- Keep secrets, real records, runtime data and backups out of Git. The pre-existing untracked `src/nul` remains unrelated and excluded; its cleanup is DEV-8.
