# Planning change log

[Back to index](index.md) | [Decision register](90-decisions-and-open-questions.md)

## 2026-10-15 — Initial planning package

- Read the supplied concept brief and preserved a copy in this folder.
- Inspected the existing Laravel/Vue foundation, routes, model/migration inventory, Docker configuration and historical setup report.
- Populated the existing empty `index.md` with navigation, implementation/validation/deployment status and delivery milestones.
- Added Parts 00–15, requirement traceability, a decision register and a reusable part template.
- Recorded the owner's confirmed choices: phased self-service and on-campus intake; initial staff-maintained/imported verified client list.
- Incorporated the owner's clarification that hosting and “online” access mean BISU's local campus network. Added internal HTTPS/network planning and operation without runtime Internet dependencies.
- Defined separate request, payment and queue histories, guarded payment/release actions and audit responsibilities as proposed design rules.
- Left office fees, identity proof, signatories, priority/refund rules and infrastructure specifics as open decisions with affected work identified.
- Recorded Docker runtime availability as unverified because the Docker engine was unavailable during inspection. Prior setup test results remain historical evidence.
- Application source, database, containers and deployment configuration were not changed by this documentation task.

## Verification of this planning delivery

- Passed: 22 Markdown files, including 16 numbered parts, are present and linked from the index.
- Passed: 147 relative Markdown file links resolve; no broken file links found.
- Passed: all 16 numbered parts contain status, dependency, owner, update and acceptance information.
- Passed: the original supplied brief is preserved verbatim after the source-file introduction.
- Reviewed: confirmed campus-network scope is reflected in architecture, intake, notifications and deployment.
- Reviewed: all concept-brief modules, objectives and expected outputs map to plans and acceptance evidence.
- No application test rerun is required for Markdown-only changes; no new runtime pass or deployment claim is made.

## Future entry format

```text
Date / author:
Work package / decision ID:
What changed and why:
Affected plans and code connections:
Validation performed and evidence:
Implementation status:
Deployment environment/revision (if applicable):
Known issue / next action / optimization suggestion:
```

## 2026-10-16 - Repository and development foundation

- Project owner supplied https://github.com/EnrieJohn31/BISU-Registrar.git and authorized publishing the existing project plus prerequisite setup before business features.
- Established the application root as the repository boundary; preserved starter Git/GitHub metadata in an ignored local backup instead of deleting it.
- Added root CI/Dependabot configuration, contribution/agent instructions, protected file-ignore rules and reproducible setup/check scripts.
- Removed runtime font CDN requirements in favor of system fonts for campus-LAN operation.
- Started Docker Desktop; local checks and remote publication evidence are maintained in docs/FOUNDATION-VALIDATION.md.
- Registrar/Cashier modules and office policies remain planned, not implemented. Campus production remains not deployed.
- Final 2026-10-16 results: setup preserves .env/key; 40 tests / 138 assertions, frontend/PHP checks and GitHub clean-checkout CI passed. Foundation revision 84db53477d2fb22492b1d6789c072c7e7c87f746; [CI evidence](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38878033808). Main protection requires PR/CI and blocks force pushes/deletion; campus deployment remains pending.

## 2026-10-16 - Login page redesign (UI only)

- Reworked `AuthSplitLayout.vue` to a shadcn-style split screen: blank blue image panel (left, desktop only), "Back to the website" link and left-aligned heading/form (right). `AuthLayout.vue` now uses it, so all `auth/*` pages share the layout.
- Simplified `Login.vue` to email, password (show/hide), remember me, forgot password, sign in and sign-up link. Passkey sign-in, Fortify routes and form field names are unchanged.
- Reference design's Google/Facebook buttons and name field were not added: no social sign-in or login-name field is planned, and account policy stays with [Part 03](03-accounts-permissions-and-audit.md).
- Validation: build, `vp check`, `vue-tsc` and 40 tests / 138 assertions passed locally; `/login` returns HTTP 200. Headless Edge screenshot of `/login` (dark mode) matched the reference layout; no campus deployment recorded.
- Next action: supply the left-panel image (placeholder comment in `AuthSplitLayout.vue`).

## 2026-10-16 - Home page branding (UI only)

- Replaced the starter's Laravel wordmark/"13" artwork and Laravel promotional copy/links (documentation, Laracasts, Laravel Cloud "Deploy now") on `Welcome.vue` with BISU Registrar content: short system description, Log in/Dashboard button, and a decorative blue panel (BISU Registrar wordmark plus a stacked document/queue-ticket/receipt illustration). The illustration is decorative only and states no workflow, ticket format or policy.
- Remaining starter branding: `AppLogoIcon.vue` (sidebar/header mark) and `public/favicon.svg`/`favicon.ico`/`apple-touch-icon.png` still use the starter logo. Replace them when the official BISU logo/seal is supplied; no official marks were invented.
- Validation: build, `vp check`, `vue-tsc` and 40 tests / 138 assertions passed locally; headless Edge screenshots (dark mode, desktop) rendered the page correctly. Light mode and real mobile widths were not visually checked.

## 2026-10-16 - Registrar supplier list and performance points (plan only)

- Project owner reported that the supplier side was missing from the plan; confirmed it was absent from all parts and from the original concept brief, so it is recorded as an owner addition (C07), not a brief requirement.
- Scoping answers: suppliers to the Registrar's Office; per-delivery weighted performance rating (criteria 1–5 → 0–100); staff-only; staff submit, supervisor confirms; low score flags For review with supervisor decision (no auto-suspension); reference numbers only, no money or Cashier link; compliance document type/expiry without uploads; Registrar-only, office-scoped; build after M2.
- Added [Part 16](16-supplier.md). Connected updates: index (Part 16 row, M3 scope), Part 02 entity row, Part 03 roles and audit events, Part 12 report line, Part 13 UAT-17–21 pointer, Part 15 navigation and future candidates, decision register (C07, D15–D17), traceability (C07).
- Validation: Markdown-only; links checked. No application code changed; no tests rerun.
- Next action: Registrar to decide D15 (criteria, weights, threshold, period), D16 (supplier fields, documents) and D17 (procurement relationship).

## 2026-10-17 - Whole-plan review, gap fixes and diagrams (plan only)

- Project owner asked for an overall check of the plan, updates for missed items, and illustrations. Findings, severities and a team check-off list are in [93-plan-review.md](93-plan-review.md).
- Added supporting files: [94](94-team-schedule-and-risks.md) (roles, iterations, decision gates, thesis checkpoints, Definition of Done, risk register), [95](95-office-interview-checklist.md) (questions per office mapped to decisions, documents to request) and [96](96-glossary.md) (glossary).
- New proposed content in Parts 01–16: Citizen's Charter as the service and processing-time source, client feedback (CSM), legally required priority lanes, privacy program items (DPO, PIA, notice versions, breach contacts), service calendar, stalled-request and unclaimed-document rules, office clearances as requirements, throttling and staff 2FA/idle timeout, browser/device baseline, security checklist, thesis evaluation and demo data, go-live cutover with parallel run, NTP, repository custody, new roadmap candidates.
- Decision register: D18 (stalled/unclaimed), D19 (feedback), D20 (browser/device baseline), D21 (repository custody); D02, D05, D06 and D09 widened; new proposed defaults. Traceability: nonfunctional N01–N08 and review additions A01–A07.
- Added 27 Mermaid diagrams across the index, Parts 01–16 and files 90, 93, 94, 95.
- Validation: relative links checked; every Mermaid diagram test-rendered with Mermaid in headless Edge (results in the session report). No application code changed; no tests rerun.
- Next action: team checks off 93; assigns roles and dates in 94; takes 95 to the offices; names an owner for each open decision.

## 2026-10-17 - Publish UI and system-plan updates

- Published the BISU welcome/login branding and shared authentication layout together with the expanded supplier, workflow, readiness, interview, schedule/risk and glossary plans.
- Validation: check.ps1 passed (Composer validation, asset/route generation, frontend formatting/lint/types, PHP formatting/static analysis, 40 tests / 138 assertions); 303 relative links across 27 plan files resolve.
- Used a feature branch and pull request for protected-main review. No campus deployment or business-module implementation is claimed.

## 2026-10-17 - Supplier terminology (C08)

- Team requested Supplier as the module name. Normalized the planning vocabulary, including prior summaries, and recorded the terminology change explicitly in C08.
- Renamed Part 16 to 16-supplier.md. Updated index/navigation, diagrams, roles/audits, proposed suppliers tables and supplier_* identifiers, permissions, action/page paths, reports, UAT references, decision items, interviews, schedule/risks, glossary and traceability.
- Scope, rating formula, supervisor controls and planned delivery order remain the same. Composer's technical dependency directory is unaffected.
- Validation: relative file links checked and stale module terms/links searched. Documentation-only; application tests are not rerun for this rename.
- Publication: new documentation pull request against protected main, following the already merged UI/plan update.

## 2026-10-17 - Part 03 slice 1 design and handoff file (plan only)

- Project owner chose Part 03 as the first build area and asked for a handoff after every completed part.
- Added the [Part 03 slice 1 design](03-accounts-permissions-and-audit.md#slice-1-design-staff-accounts-roles-and-audit): scope, starter-code facts, RBAC comparison (new D22, native recommended), 10 staff roles, permission catalogue across all parts, field-level data model, authorization flow, staff provisioning without email, account lifecycle, audit design, starter changes, screens, code layout, tests and seven questions to settle before coding.
- Added [handoff.md](handoff.md): one current handoff, rewritten after each part; history stays in this log. The index's next-actions section now points to it; the Definition of Done (94) and AGENTS.md require updating it.
- Connected updates: D03 widened (staff account policies), D22 added; Parts 00 and 02 linked to the slice design; index focus and Part 03 row updated.
- Validation: relative links and anchors checked; new Mermaid diagrams test-rendered with Mermaid 10.9.1 and 11.4.1 in headless Edge. Documentation only; no application code changed.
- Next action: team answers the slice 1 questions in handoff.md, then slice 1 is coded on a feature branch.

## 2026-10-17 - Part 03 slice 1 implemented (local branch)

- Project owner approved all slice 1 recommendations (C09) and asked for development on a local branch only, with nothing pushed, plus local test accounts in a git-ignored Credentials.md.
- Implemented on `feat/part-03-accounts-roles-audit`: offices, role assignments with history, audit events (four additive migrations); native enum roles and permission catalogue; account actions with audit; middleware for deactivated accounts, staff idle timeout, forced temporary-password change and required two-factor; admin screens for users, roles, status, credentials and the audit trail; `bisu:create-admin`; `DevelopmentAccountsSeeder`. Self-service account deletion removed; shared `auth` props use an explicit allow-list.
- Design change: sensitive roles require confirmed TOTP two-factor; a passkey alone does not count (it does not protect password sign-in). Recorded in C09 and Part 03.
- Validation: `./check.ps1` passed (97 tests / 498 assertions, 57 new; Larastan level 7; lint, formatting, `vue-tsc`, build). Migrations applied, rolled back and re-applied on development MySQL. End-to-end smoke test on the development stack passed. New screens not visually reviewed.
- Next action: team reviews and visually checks the screens using the test accounts, then merges after the supplier-rename PR; offices confirm the role catalogue and D03 values.

## 2026-10-17 - Restore stopped Node/Vite service

- Diagnosed bisu-registrar-node-1 as stopped with exit code 1 and npm SIGTERM; Docker reported OOMKilled=false and no startup error. The exact caller of the stop is not identifiable from the available events/logs.
- Restarted the existing service with docker compose -f docker-compose.dev.yml up -d node.
- Verified HTTP 200 from localhost:5178/@vite/client, localhost:5178/resources/js/app.ts and localhost:8085/login; Docker reports Node running.
- No application or container-configuration changes required. Updated the current handoff with recovery commands; no tests rerun for the runtime/documentation-only fix, and nothing committed or pushed.

## 2026-10-17 - Development environment performance

- Project owner reported slow pages and black screens. Measured on the Windows host: 3–7 s per request even for `/up`, and up to ~45 s of blank page after a Vite restart.
- Cause: PHP and Vite read their dependencies through the Windows bind mount. A page loads ~850 PHP files, 96% of them from `vendor/`, and OPcache re-checks each file's timestamp. A test server with OPcache timestamp checks off answered in 0.12 s; one with only `vendor/` on native storage answered `/login` in 0.23 s instead of 4.8 s.
- Change (dev only): `vendor` and `node_modules` named volumes for the app and node services, `PHP_CLI_SERVER_WORKERS=4` for parallel requests, and `check.ps1` now checks dependencies inside the container. CI and `setup.ps1` already install inside the container, so they need no change. Production deployment is unaffected.
- Result: `/login` 0.21–0.25 s and `/up` 0.11 s from the host; Vite ready in ~1 s; browser load 2–3 s with no errors; `check.ps1` passed in 66 s (97 tests / 498 assertions).
- Branch `chore/docker-dev-performance`, local only, not pushed.


## 2026-10-17 - Supplier rename published

- Merged [PR #5](https://github.com/EnrieJohn31/BISU-Registrar/pull/5), revision `22d284d`, after required [Foundation checks](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38991009396) passed. Supplier terminology and linked plan references are now on main; scope remains C07/C08.
- Rewrote handoff with the completed publication, ongoing Part 03 browser review and owner-selected Part 04 design preparation (no office documents available yet).
- Campus staging/production remain not deployed; office policies and acceptance remain pending.

## 2026-10-17 - Part 03 design published

- [PR #6](https://github.com/EnrieJohn31/BISU-Registrar/pull/6) merged as `2cd523e` after [Foundation checks](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38999332148) passed. Published the staff account/role/audit design and rolling-handoff convention before its implementation.
- Rewrote handoff for the completed step; implementation/browser review and Part 04 draft preparation continue. Campus deployment and office acceptance remain pending.

## 2026-10-17 - Part 03 browser review completed

- Completed isolated headless Edge review at desktop/mobile widths and light/dark themes with synthetic accounts. All 19 workflow/layout checks passed, including TOTP reset and temporary-password reissue; disposable accounts deactivated afterward. Redacted local screenshots inspected; [review record](../docs/PART-03-BROWSER-REVIEW.md) documents limits.
- Fixed audit rows closing immediately on Enter, added Space activation, wrapped mobile expiry/long account text and removed Laravel starter sidebar links. Connections to existing Inertia actions and server authorization preserved.
- Found stale Vite source across Windows Docker bind mounts; added opt-in one-second polling in dev Compose/Vite. Verified served modules update after host edits. No dependency upgrades or production hosting changes.
- Full check.ps1 passed again: 97 tests / 498 assertions, Larastan level 7, PHP/frontend formatting, lint, Vue TypeScript and build. Rewrote handoff after completion; office UAT and campus deployment still pending.

## 2026-10-17 - Part 04 detailed design prepared

- Owner selected client/import design because no office documents are available. Expanded Part 04 into profiles, reviewed imports and later account-linking slices, with data constraints, fixed draft CSV contract, Laravel/Inertia boundaries, audit/privacy handling, concurrency/replay/recovery and acceptance cases.
- Checked current Part 03 role/permission map: Registrar office scope is mandatory even though Cashier supervisors also have clients.import; independent review/commit and any broader duties are proposals awaiting office confirmation. No enum/role changes made.
- Connected architecture/index and rewrote handoff. D03/D04/D09 approval gates remain open; template/limits/proof are not BISU policy. No Part 04 migrations/routes/features or real-data imports added. Existing application checks remain valid for this documentation-only step; Markdown links checked separately.

## 2026-10-17 - Part 03 accounts and browser fixes published

- Part 03 accounts and browser fixes published: [PR #7](https://github.com/EnrieJohn31/BISU-Registrar/pull/7) merged as `fa2246a` after required [Foundation checks](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/38999900147) passed.
- Rewrote handoff after the completed publication. Campus staging/production remain not deployed; office policy and acceptance decisions remain open.


## 2026-10-17 - Docker development performance published

- Docker development performance published: [PR #8](https://github.com/EnrieJohn31/BISU-Registrar/pull/8) merged as `4d69e06` after required [Foundation checks](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/39000107485) passed.
- Rewrote handoff after the completed publication. Campus staging/production remain not deployed; office policy and acceptance decisions remain open.

## 2026-10-17 - Final status reconciliation and source-watching follow-up

- Reconciled Part 00/02/03/index/README with the merged accounts implementation and completed browser QA; preserved historical foundation results. Handoff rewritten with completed work, publication evidence, explicit next slice and outstanding office gates.
- Recorded Edge 155.0.4283.45 and successful Enter/Space audit-panel regression. Source polling is the only runtime-code follow-up after the full local checks; all code changes were included in that passed check run.
- Final follow-up combines the small Docker source-watch fix, review evidence and Part 04 design documentation; protected-main CI/merge evidence is linked from handoff. No client features implemented, no production deployment, no credential or nul artifact staged. Existing Dependabot updates remain outside scope.
## 2026-10-17 - Git policy: local commits only (documentation)

- Project owner instructed that work is committed to local branches only; nothing is pushed, no pull requests are opened and nothing is merged on GitHub unless the owner explicitly asks.
- Recorded in `AGENTS.md` and at the top of the current handoff. Checked state: `main` equals `origin/main`, every local branch's commits are already in `main`, nothing is waiting to be published. The earlier publication table in the handoff is history from before this rule.
- Handoff next steps clarified: the first step is owner approval of Part 04 slice 1 (synthetic data only), recorded as a decision, then local implementation.
- Documentation only; no application code or tests changed.

## 2026-10-17 - Part 04 slice 1 implemented (client records, local branch)

- Project owner approved Part 04 slice 1 and asked for it to be built and testable on their side, local commits only. Recorded as decision C10 with the implementation details added beyond the written design.
- Implemented on `feat/part-04-client-records` (not pushed): additive migrations for client profiles, identifiers and revisions; models, actions, policy, requests and controllers; Registrar "Client records" screens (list/search, create, detail with correction, verify, flag, add/retire ID and history); sidebar group and dashboard card; `DevelopmentClientsSeeder` with 20 clearly fake demo clients. Existing permissions only; no Part 03 role change.
- Rules enforced on the server: IDs as exact text with a database unique key that includes retired IDs; optimistic version on every change; verification as a controlled action; correction of a verified profile returns it to needs-review; last current ID protected; audit holds no names or full IDs.
- Validation: `./check.ps1` passed (161 tests / 844 assertions; 64 new). Full suite also passed on a disposable MySQL database, which exposed a too-long index name (fixed) and four tests that assumed JSON key order (fixed, two were Part 03 tests). Parallel race probes on MySQL showed one winner each for same-ID creation, stale saves, cross-profile ID adds and concurrent retirement, with no duplicates or version drift. A 34-check Edge walkthrough passed. Evidence: [docs/PART-04-SLICE-1-VALIDATION.md](../docs/PART-04-SLICE-1-VALIDATION.md).
- Not covered: office or custodian acceptance, real data, official ID format (D04), retention/privacy review (D09), other browsers and kiosks (D20). Imports and account linking remain design only behind D03/D04/D09.
- Next action: owner tests the screens with the demo accounts; Part 05 or Part 04 slice 2 follows when office inputs arrive.


## 2026-10-17 - AI co-author attribution cleanup (local / publication pending)

- Owner requested removal of the Claude contributor credit. Found five co-author trailers: two unpublished, three in published history; human author/committer identities were already correct.
- Backed up all Git refs and cleaned the two local commits atomically, preserving every file tree, author and timestamp: 878964d -> 8402d30; cca3936 -> d577d76. Added the future no-AI-credit instruction to AGENTS.md and rewrote handoff.
- Prepared and verified an isolated metadata-only rewrite of the three published trailers and descendants (20 commit objects, five existing published branch tips); all trees identical. Four signatures would be lost on changed commit objects. Recorded map/backup and exact publication/recovery plan in docs/CONTRIBUTOR-ATTRIBUTION-CLEANUP.md.
- No push, PR or branch-protection change. Main blocks force-pushes and enforces PR/CI for admins; explicit approval is needed for its temporary protection adjustment/history replacement. Part 04 stays local and ready for owner testing. No application code changed; existing test evidence preserved.

## 2026-10-17 - Approved published cleanup completed; human authors required

- Owner approved guarded history replacement and confirmed authors/co-authors must be actual people, not AI providers. Published five prepared tips atomically; clean main 2e83568 has identical application content. Every original protection setting restored and verified. Four original signatures removed only on rewritten commit objects, as approved.
- Aligned local branches without file/index changes; Part 04 implementation now 0ae71f6 on the cleaned base and remains unpushed. Recovery bundles, protection snapshots and commit maps retained locally. No reachable local Claude trailers; GitHub API checked 24 public commits with human author names only; clean-main Foundation CI passed (run 39033220866).
- Recurrence prevention in separate PR #10: commit-msg/pre-push guards, safe installer/setup integration, required-CI attribution check, 16 behavioral cases and 11 integration/configuration checks. Updated AGENTS.md to explicit human authorship and rewrote handoff/review record. No application feature changes or campus deployment.

## 2026-10-17 - Approved contributor cleanup and future attribution guards

- Owner explicitly approved removing published Claude co-author credit and temporarily adjusting main protection. Atomic, exact-lease updates replaced five existing public tips; clean main is 2e83568. Every rewritten tree matched the original; human identities/times were preserved. Original main protection restored and verified. Backup bundles/maps remain ignored and local.
- Added commit-msg/pre-push guards, shared attribution checker and 16 behavioral cases; setup installs the local hooks via install-git-hooks.ps1. Existing custom hooks are preserved. Required Foundation CI checks primary authors and co-author trailers across fetched history. AGENTS.md records no automatic AI credits and local-only publication policy.
- Application code is unchanged. Part 04 implementation stays local and separate from this prevention branch. GitHub contributor displays may take about 24 hours to refresh; original PR/commit references may retain historical objects.
- Owner clarified that project authors/co-authors must be actual people, not AI providers. Made that rule explicit and added provider-name rejection cases (OpenAI, Anthropic, Google Gemini, Cursor).

## 2026-10-17 - Human-authorship guards merged and activated

- PR #10 merged as c331ae3 after required Foundation checks passed (run 39033657347). Merged only prevention/tooling/docs into the local Part 04 branch; preserved its implementation, private validation and all existing change-log entries.
- Activated commit-msg/pre-push guards in this checkout. Human authors/co-authors are required; recognized AI provider/tool credits are blocked locally and by required CI. Sixteen behavioral cases and eleven integration/configuration checks passed. Main protection remains restored; no additional history rewrite or production deployment.
- Part 04 code remains unpushed. Existing clones must synchronize cleaned history and activate hooks; GitHub contributor caches can take about 24 hours to refresh.


## 2026-10-17 - Disable Claude Code default commit/PR attribution (local configuration)

- Owner asked how to prevent recurrence when using Claude. Verified active commit/push hooks and existing required CI, installed Claude Code 2.1.120, and the official attribution configuration.
- Added project-scoped .claude/settings.json with empty attribution.commit / attribution.pr strings. Both must be set; omitted fields keep defaults. Avoided attribution:false because the official reference requires v2.1.281+ for that syntax. No global settings or session metadata changed.
- Updated README, handoff and attribution record. JSON validated and Git whitespace checks passed; application code and guard code unchanged. Local commit only; no push, PR or publication of Part 04 implementation.
## 2026-10-17 - Requirements checklist for the offices (documentation)

- Project owner asked for an A4 list of what the team must collect before building further, to send to the team.
- Added [requirements.pdf](requirements.pdf) (3 pages, 29 items in three priorities, plus a documents list and tracking lines) with its editable source [requirements.html](requirements.html), built from the open decisions in [90](90-decisions-and-open-questions.md) and the [office interview checklist](95-office-interview-checklist.md). Each item names the decision or part it unlocks. Ground rules: blank or made-up examples only, official versions, and who/when/which document recorded for every answer.
- Received so far: Citizen's Charter 2024 (1st Edition), page 115 only (Registration Office, 5.2.1 Enrollment). It shows the charter's structure (office, classification, type of transaction, who may avail, requirements and where to secure each) but not the steps, fees or processing time, and Enrollment is probably outside this system's scope (D01 question).
- The PDF was produced by printing the HTML from Microsoft Edge; layout checked page by page. Documentation only; no application code or tests changed.

## 2026-10-18 - Work plan while waiting for office inputs (documentation)

- Project owner asked what the team can do while the offices answer the requirements checklist.
- Added [97 - Work while waiting](97-work-while-waiting.md): four tracks (development, thesis and documentation, office preparation, deployment readiness) with size, rework risk and the office input each task later takes, plus a suggested order and a "not yet" list (Parts 06, 08, 09, imports and linking wait for answers). Chosen rule: build mechanisms with labelled demo data, not office rules.
- Linked it from the index and the handoff (new step 4). Documentation only; no application code or tests changed.

## 2026-10-18 - Part 04 follow-up QA and scoped fixes (DEV-1)

- Reviewed the index, Part 04, waiting-work plan and decision C10. Tested the existing slice and fixed two confirmed defects: profile history beyond the latest 50 revisions was unreachable, and free-text client reasons could expose names/full IDs to general audit viewers.
- Added authorized history pagination using the shared Vue component and the existing profile/revision index. The central recorder now preserves the full reason only in restricted revision history; general audit events reference the revision. No migration, permission change, historical audit rewrite, office-rule change, import or account-linking implementation.
- Added two regression tests, confirmed both failed before the fixes, and strengthened existing privacy/access assertions. Coverage includes 51 revisions, record isolation, Intake access, every role on older pages, all six mutation reasons and both administrator/supervisor audit responses. Independent code review found no remaining regression in the changed files.
- Baseline `./check.ps1` passed: 161 tests / 844 assertions. Final complete checks passed: **163 tests / 936 assertions**, production build/Wayfinder, frontend formatting/lint/types, Pint and Larastan. Part 04 against disposable MySQL 8.4 passed: **45 tests / 394 assertions**; the database was removed after testing. Existing demo records were not reset or edited.
- Browser confirmation remains pending: the computer-use tool exposed no apps/browsers. The 2026-10-17 Edge walkthrough/race results remain historical evidence, not a new browser or concurrency pass. Office acceptance and D03/D04/D09 remain open.
- Updated Part 04, index, waiting-work tracking and README; rewrote the handoff. Full evidence and connected files: [follow-up QA report](../docs/PART-04-QA-2026-10-18.md). Work is on local branch `fix/part-04-qa-2026-10-18`; no push, PR, merge or campus deployment.
- Next useful optimization: save the browser walkthrough under DEV-3; keep bounded history pagination and reuse its existing index. DEV-2 worker/scheduler/Mailpit design is the next development task. The unrelated, pre-existing untracked `src/nul` remains excluded for DEV-8 cleanup.

## 2026-10-18 - DEV-2 development background services and waiting-work boundary

- Owner authorized the next recommendation and limited current work to items that can proceed while offices answer. Recorded C11 and updated the waiting-work plan: incoming answers do not automatically authorize a gated feature. Imports/linking, office workflows, real fees/IDs/templates and campus deployment retain their own gates.
- Documented the scoped design, then added a database/default queue worker, `schedule:work` and pinned Mailpit to development Compose. App/Vite/worker/scheduler reuse one built PHP image and existing volumes. SMTP stays inside Docker; the temporary test inbox binds to loopback at http://localhost:8026. No relay, business schedule, package or migration was added.
- Added Mailpit settings to `.env.example`. Existing environment files are preserved by setup; only the selected local test-mail/reservation values were changed separately for this session. Live probing caught an empty `MAIL_URL` overriding SMTP; changed it to `null` and verified delivery/retry/failure again.
- Setup pauses app/Vite/worker/scheduler during dependency replacement and explicitly recreates them against the rebuilt image, fixing stale Docker image references observed during final review; checks pause/restore running Vite/worker/scheduler. Complete setup passed and preserved the existing `.env` byte for byte. A harmless injected failure proved restoration and preservation of an intentionally stopped scheduler.
- Live synthetic probes confirmed dedicated-worker execution, exactly one captured demo email after recovery on attempt 2, exhaustion after attempt 3 and a stored failed job. Only disposable failed UUIDs/markers/scripts were removed; pending/failed queues returned to zero. Scheduler tick observed with no business tasks. Final `./check.ps1` passed: **163 tests / 936 assertions**, build/Wayfinder, frontend/PHP formatting/lint/types and Larastan. Background services restored afterwards.
- Updated connected Parts 04/11/14, README, decision register, index and waiting-work tracker; rewrote the handoff. Detailed design, commands, image digest, validation and limits: [DEV-2 report](../docs/DEV-2-BACKGROUND-SERVICES.md). Current branch `feat/dev-background-services-2026-10-18` is local only; no push, PR, merge or campus deployment.
- Next useful optimization: DEV-3 saved browser regression tests. Future long jobs require measured timeouts/idempotency; keep the worker timeout below reservation expiry. The pre-existing untracked `src/nul` remains excluded for DEV-8 cleanup. Client/account records and authentication/permission connections are unchanged.

## 2026-10-18 - DEV-3 saved browser regression suite

- Owner authorized the next eligible waiting-work item. Recorded C12 and preserved C11: labelled synthetic fixtures and existing rules only; office-dependent features and later design/approval gates remain closed.
- Saved 34 named Playwright cases reconstructing the documented walkthrough and adding history/audit/keyboard regressions. A separate fixed Compose project prepares allowlisted source, locked dependencies, assets and SQLite in a disposable workspace, with random credentials/keys/TOTP and real authentication. Four negative seeder guards join the PHP suite; no development environment, database, volumes, credentials or runtime data is reused.
- Full browser matrix passed: **136 checks** across desktop/phone Chromium emulation and light/dark themes, zero retries. History reaches the creation revision on page 2 and returns; restricted reasons remain outside the general audit UI. Screenshot review found a detail-capture timing gap in the test, corrected with heading/full-ID assertions. Pagination checks now compare record links rather than whitespace-sensitive text.
- Final strengthened B21/B27/B33 assertions passed **12/12** in all four projects; refreshed detail screenshots were reviewed. Retained the full-matrix report alongside the final focused report. Formatting/lint and browser TypeScript passed again for the final assertions.
- Final `./check.ps1` passed: **167 tests / 944 assertions**, build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan level 7. Both Compose files, PowerShell/shell syntax and workflow YAML validated. Running background services were restored. Development environment hash and record-count/version aggregates were unchanged across isolated full runs; browser containers/network/workspace cleaned up on completion and observed failures.
- Added an independent CI browser job with seven-day synthetic artifact retention, but no GitHub run/upload occurred. Reports/screenshots/traces remain ignored locally. Updated README, Parts 04/13, decision register, index and waiting-work tracking; rewrote the handoff. [Design, cases and evidence](../docs/DEV-3-BROWSER-REGRESSION.md). Local branch: `test/browser-regression-2026-10-18`; no push, PR, merge or campus deployment.
- No client-record application defect or office-rule change was needed. Existing registration-disabled asset builds fail on a missing generated registration route; recorded for a separate technical fix before using that setting. Physical campus browser/device acceptance and MySQL race evidence remain separate. Next eligible listed work: DEV-8 supplied official branding and stray-file cleanup. Useful optimization: test-only package-download caches if fresh installs become slow; never share writable development dependency/runtime volumes.

## 2026-10-18 - DEV-8 BISU branding and stray-file cleanup

- Owner authorized the next eligible waiting-work item (C13), preserving C11 and office/design gates. No official logo was present locally; verified the university's link to the official Balilihan site and sourced its existing full-size seal from public campus media. Preserved the downloaded PNG/hash/provenance; no new mark was designed.
- Replaced `AppLogoIcon` and the sidebar mark with a local 96-pixel derivative, retaining the shared prop/attribute contract, app name and generated dashboard link. Removed monochrome SVG styling from connected header/alternative authentication layouts. Replaced PNG/ICO/touch browser icons in the common Blade shell with fresh cache keys; removed the starter SVG. No external image request, application package, controller, route, permission, schema or office rule change.
- Added a Windows icon generator using `System.Drawing`; repeat runs produced identical hashes. PNG dimensions/transparency and all three ICO frames were decoded/verified. Kept the full 411,527-byte source outside public assets; the shared display derivative is 21,556 bytes.
- Removed only the known untracked `src/nul` diagnostic file after verifying its exact workspace path and absence of application references; added an exact ignore entry. No runtime records, credentials or database were removed or reset.
- Validation: full `./check.ps1` passed **167 tests / 944 assertions**, build/Wayfinder, formatting/lint, Vue/browser TypeScript, Pint and Larastan. The existing saved browser suite passed **136 checks**, zero retries. One-off isolated browser inspection and six reviewed captures verified desktop expanded/collapsed and phone drawers in both themes; browser icon responses match committed bytes and public/login pages use the same links. Development environment hash and record aggregates were unchanged; temporary browser runtime removed and background services restored.
- Updated baseline/index/waiting-work tracking, decision register and README; rewrote the handoff. [Source, design, commands and evidence](../docs/DEV-8-BISU-BRANDING.md). Branch `feat/bisu-branding-2026-10-18` remains local only; no push, PR, merge, GitHub run or campus deployment.
- Next eligible task: DEV-4's two-renderer PDF comparison on a fictional certificate stamped “SAMPLE – NOT VALID”. Real templates remain D08-gated; Part 05 still needs its design approval. Optimization: retain the small shared derivative and local assets; use a dedicated small official icon later if MIS supplies one. Physical campus browser/device acceptance remains open.

## 2026-10-18 - DEV-4 PDF renderer comparison and current project handoff

- Owner authorized DEV-4 and explicitly requested an updated `handoff.md` showing where work stopped. Recorded C14 and preserved C11's waiting-work boundary. No office-dependent workflow or application PDF integration was authorized.
- Added an isolated root runner/Compose project and separate locked Dompdf 3.1.6 / Playwright 1.64.1 candidates under `experiments/pdf-renderers/`. Both use identical bundled DejaVu fonts and synthetic HTML/images, with no runtime networking, read-only roots and bounded CPU/memory/process time. No `src/` source, app package, environment, database, route, permission, queue job or office rule changed.
- Final matrix passed: **12 fixture/engine combinations, 36 fresh-instance/browser renders, 19 reviewed pages** across A4/Legal, standard/long accented names and a 60-row repeating table. All names/rows, paper sizes, stamps/footers, embedded fonts/local images and page bounds passed. Final mixed-paper packs also passed text/font checks and pixel-identical comparisons with reviewed source pages.
- Visual review caught a Chromium fixed banner displaced into the footer even though text extraction passed; used its margin-header API and added a top-margin coordinate check. Normalized legitimate wrapping after the long name's existing hyphen. Avoided a disabled-script style-injection wait by direct automation DOM removal; added 15-second page/action and 180-second process bounds. Refreshed only owned artifacts and removed stale success manifests before reruns.
- Provisional recommendation: Dompdf for simple certificates (observed per-fixture medians 201–426 ms versus Chromium 512–596 ms). Chromium remains the alternative for more complex browser layouts. Legal pagination differed (three Dompdf versus two Chromium pages), so D08 actual-template/font/printer acceptance and RK12 remain open. Raw timings, hashes, image versions and findings are in [the report](../docs/DEV-4-PDF-RENDERERS.md) and dated experiment baseline.
- `./check.ps1` passed **167 tests / 944 assertions**, Composer/build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan. Background services restored. Existing 136-check browser evidence remains DEV-8 historical validation; no app screen changed, so it was not rerun. Generated PDF packs/previews stay ignored in `output/pdf/`; temporary inputs/source PDFs are removed after verification.
- Updated Parts 10/13, RK12, index, decision/waiting-work registers and README; rewrote the handoff with a project-status table, completed slices, local revisions, open office decisions and exact resume steps. **Next: DEV-5 Part 05 design only, then owner review before implementation.** Local branch `spike/pdf-renderers-2026-10-18`; no push, PR, merge, GitHub run or campus deployment.
- Useful optimization for later scoped implementation: approved font-metric caching, measured worker concurrency and an interchangeable renderer boundary. No production workload, signing/PDF-standard claim or document lifecycle is inferred from this spike.

## 2026-10-18 - Part 05 slice 1 design (DEV-5, documentation)

- Wrote the [Part 05 slice 1 design](05-services-requirements-and-fees.md#slice-1-design-catalog-core-dev-5-2026-10-18): catalog core with separately versioned content and fees, immutable published versions with a start date only, requirement groups chosen by the client's situation, three requirement kinds, fee models free/fixed/per-unit, integer money, snapshot and fee-at-endorsement code for Parts 06, 08 and 09, office-scoped permissions, screens, five labelled demo services, validation plan, seven proposals to approve (proposed C15) and eight office questions.
- Verified against page 115 of the Citizen's Charter 2024 (1st Edition, Enrollment). The page shows the entry header and grouped requirements with "How to Avail", but not the steps, fees, processing time or person responsible. Corrected [97](97-work-while-waiting.md), which had said otherwise, and deferred the step table to slice 2.
- Proposed one catalogue addition, the read-only `services.view` (added to the Part 03 catalogue as proposed). Updated Part 02's table row, the decision register (D05 note and a proposed-defaults entry), the index and the handoff. Three Mermaid diagrams in Part 05 were rendered to confirm they parse.
- Documentation only; no application code, migration or test changed. Nothing is approved or built until the owner approves the design. Local commit only.

## 2026-10-18 - OPS-1 same-network phone preview preparation

- Owner asked to access the app from a phone on the same network. Recorded C16 for this reversible development preparation; proposed C15/Part 05 and every office gate remain unchanged.
- Added `lan-preview.ps1` and optional `docker-compose.lan.yml`: validate a private IPv4 address assigned to an active PC adapter, build compiled assets, stop Vite, retain localhost and add only app port 8085 on that LAN address. App/worker/scheduler use the LAN URL and debug disabled. Stop restores base loopback containers and Vite; a failed startup attempts the same restoration. No source `.env`, app package/source, migration, account/role or business setting changed.
- Added [phone setup, scoped Windows firewall, undo and troubleshooting instructions](../docs/LAN-PHONE-PREVIEW.md). Mailpit and Vite keep loopback bindings; MySQL/Redis remain unpublished. The agent has no Windows administrator token, so no firewall rule or network-category change was made.
- Validation: runner syntax/Compose passed, production build/Wayfinder passed, login and all 28 compiled asset references returned HTTP 200 through LAN and localhost, and no HMR URL remained. Stop restored seven base services and loopback-only app binding; the source `.env` SHA-256 remained identical. Final preview is left running with six services and Vite paused for phone testing. Historical foundation/browser evidence was not rerun for this root-only slice.
- OPS-1 remains open for actual phone/second-laptop access and model/OS/browser-version records. Updated Parts 14/97, index, decision register, README and handoff. Local branch `chore/lan-preview-2026-10-18`; no push or campus deployment. If frequent device testing warrants it, a separate preview image/service could avoid pausing Vite.

## 2026-10-18 - OPS-1 owner confirms phone login page access

- Owner answered “Login page opens” to the same-Wi-Fi check at http://192.168.1.8:8085. Recorded this as physical phone reachability evidence, without assuming successful sign-in, navigation, device/browser versions or a firewall change.
- Updated the handoff, index, Part 14, waiting-work status and preview report. OPS-1 remains partial until phone sign-in/navigation, model/OS/browser details and second-laptop access are checked. No application/runtime/account change; all 133 local Markdown links in six affected documents resolved and `git diff --check` passed. Part 05 still awaits proposed C15 approval. Local commit only; no push or campus deployment.

## 2026-10-18 - Baseline observation kit (OF-4, documentation)

- Project owner chose to start the "before" baseline. Added [baseline-kit.pdf](baseline-kit.pdf) (7 A4 pages; editable source [baseline-kit.html](baseline-kit.html)): what is measured and why, privacy rules, an office permission form, observation protocol, and four sheets (arrivals tally, timed sample, records extract, day sheet), plus header-only [CSV templates](baseline-templates/README.md).
- Measures: clients per day and hour, waiting time and service time (median and 90th percentile), share leaving before service, turnaround days per document type, transaction mix and priority-lane use, and how clients are lined up today. They feed Parts 07, 12, 13 and the Part 14 cutover comparison.
- Safeguards: no names, ID numbers, receipt numbers or recordings; office head signs first; Data Protection Officer and adviser informed; records extract limited to dates, types and counts and optional for the office. The sample (about five days, two timed hours a day per office) gives a picture, not statistical proof; the adviser should agree the method.
- Nothing has been observed or sent. The proposed windows, codes and sample sizes are defaults for the team and adviser to change. Documentation only; the PDF was printed from Edge and checked page by page. No application code or tests changed.
