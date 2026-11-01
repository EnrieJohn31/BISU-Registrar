# BISU Registrar — Overall System Status

**As of:** 2 November 2026 · **Stage:** Milestone M1 (rules and secure data foundation), development only · **Deployed on campus:** No

This is a snapshot for the project owner, the team and any AI assistant asked to review the project. It is written so a reader with no other context can judge the system. The living trackers are [System_Plan/index.md](System_Plan/index.md) (status by part) and [System_Plan/handoff.md](System_Plan/handoff.md) (current position and next steps). If this file disagrees with them, they win.

No passwords, secrets, real student records or personal data are in this file.

---

## 1. What the system is

**Integrated Registrar–Cashier Document Request, Queuing and Transaction Management System** for **Bohol Island State University (BISU) – Balilihan Campus**. App title: **BISU Registrar**. It is also the team's thesis project.

**The problem it solves.** Students and alumni request documents from the Registrar's Office (for example certifications and transcripts), and some requests must be paid at the Cashier's Office. Today this is done in person, with queues and paper. The system will:

1. Let clients (students/alumni) request documents on the campus network, or have staff enter the request for them at a counter (assisted intake).
2. Manage queues and counters (ticket numbers, call next, display screen, kiosk later).
3. Move each request through the Registrar (review, assessment, endorsement), to the Cashier (payment recorded and verified), and back to the Registrar (document generated, approved, released).
4. Keep a verified list of students/alumni, maintained or imported by authorized staff.
5. Record everything in an audit trail, and produce reports.
6. Keep a Registrar supplier list with per-delivery performance points (Part 16, after the first full transaction works).

**How a request moves (planned):**

```text
Client (portal or counter) → Queue → Registrar review → Cashier payment (if paid) → Registrar → Document → Release
```

**Hard constraints (confirmed decisions):**

- Hosted on **BISU's campus LAN only**. "Online" means a browser on approved campus Wi-Fi/LAN, not the public Internet (C04). No runtime dependence on Internet services.
- Built on the existing **Laravel + Vue/Inertia + TypeScript + Tailwind + shadcn-vue** app (C01).
- Staff never act on their own requests. Sensitive roles must use authenticator-app two-factor (C09).
- **Synthetic data only** until the offices approve real fields, sources and privacy rules. Real services, fees, ID formats, templates and signatories come from the offices, never from the developers.
- Each feature slice gets a written design and owner approval (a numbered "C" decision) before it is built.

## 2. Technology

| Layer | What is used |
|---|---|
| Backend | PHP 8.3+, Laravel 13, Laravel Fortify (sign-in, two-factor, passkeys), MySQL 8.4, Redis |
| Frontend | Vue 3 + Inertia 3, TypeScript, Tailwind CSS 4, shadcn-vue components, Wayfinder (typed route helpers generated from Laravel routes), Vite |
| Roles and permissions | Native PHP enums (10 roles, 40 permissions), office-scoped policies, no third-party permission package |
| Development stack | Docker Compose: app, Vite, MySQL, Redis, Mailpit (test email inbox), queue worker, scheduler |
| Checks | PHPUnit feature/unit tests, Pint, Larastan, Vite+ lint and format (`vp check`), vue-tsc, Playwright browser suite, GitHub Actions CI on pull requests |

## 3. Overall progress

| Milestone | Scope | State |
|---|---|---|
| M0 Development foundation | Part 00 | **Done** (2026-10-16) |
| M1 Rules and secure data foundation | Parts 01–05 | **In progress.** Accounts/roles/audit and client records done (first slices). Services and fees: design ready, awaiting owner approval |
| M2 First office transaction | Parts 06–09 | Not started |
| M3 Complete functional scope | Parts 07, 10–12, 16 | Not started |
| M4 Campus staging acceptance | Parts 13–14 | Not started |
| M5 Campus production and handover | Parts 14–15 | Not started |

### By part

| Part | Area | Status |
|---|---|---|
| 00 | Application baseline (Laravel/Vue/Docker) | Done |
| 01 | Requirements and shared workflow | Plan drafted; office review pending |
| 02 | Architecture and data model | Plan drafted; built piece by piece with each part |
| 03 | Accounts, permissions, privacy and audit | **Slice 1 done** and merged |
| 04 | Verified client records and imports | **Slice 1 (client records) done** and merged. Imports (slice 2) and account linking (slice 3) designed only |
| 05 | Services, requirements and fees | **Slice 1 design ready, waiting for owner approval (C15)**; not built |
| 06 | Campus portal and assisted intake | Not started |
| 07 | Queues, counters, display and kiosk | Not started (a basic-queue design, DEV-7, can be written next) |
| 08 | Registrar workspace | Not started |
| 09 | Cashier and payment control | Not started |
| 10 | Document generation and approval | PDF tool experiment only (Dompdf provisionally chosen); no module |
| 11 | Release, tracking and notifications | Not started |
| 12 | Reporting and analytics | Not started |
| 13 | Testing and campus acceptance | Automated and browser tests running for what exists; office acceptance later |
| 14 | Campus deployment and operations | Development environment only; campus servers not provided yet |
| 15 | Training and handover | Not started |
| 16 | Registrar supplier list and performance points | Scope confirmed (C07); scheduled after M2 |

## 4. What works in the system now

### Sign-in and account security

- Sign-in with email and password; password reset; email verification; profile and appearance (light/dark) settings.
- **Two-factor authentication** with an authenticator app (6-digit code) and one-time recovery codes. It is **required** for System administrator, Registrar approver, Release officer, Cashier, Office supervisor, Report exporter and Supplier supervisor. Those users are sent to set it up before they can do anything else. Passkeys can be added but do not replace two-factor.
- Staff accounts sign out after **30 minutes idle**.
- New staff get a **temporary password (valid 72 hours)** and must replace it at first sign-in.
- Deactivated accounts cannot sign in by any method, and an open session ends on its next request.
- No self-service account deletion (accounts with records are closed by the office and deactivated by an administrator).
- Self-registration is on for clients. A self-registered account has no role and cannot see staff screens. The client portal is not built yet.
- **Development only (DEV-10, on its own branch, not merged yet):** an "Email me the code" button sends the current two-factor code to the local test inbox, for testers without an authenticator app. It only works on a local development machine and never on a campus server.

### Account administration and audit trail (System administrators)

- Staff list with search and filters; create staff; edit details.
- Assign and revoke roles (with office), each with a written reason and history (who, when, why).
- Deactivate and reactivate; issue a new temporary password; reset two-factor. All require a reason and are audited.
- An administrator cannot change their own roles.
- **Audit trail viewer** with filters. Administrators see everything; office supervisors see only their own office's events. Refused access attempts are recorded too.
- The first administrator is created with a console command (`php artisan bisu:create-admin`).

### Client records (Registrar's Office)

- Search students/alumni by name or the start of an ID, filter by category and verification state, page through results. Lists show **masked IDs**; the detail page shows full IDs.
- Create a client with a first ID; correct name or category; add a second ID (for example an Alumni ID after a Student ID); retire an ID; verify a profile; flag one for review.
- Full change history per client: version, who, when, why, old and new values.
- Rules enforced on the server: IDs are text (leading zeros kept) and never reused; people are never merged by name; stale screens are rejected; correcting a verified profile returns it to "needs review"; the general audit trail never holds names or full IDs.
- Access: view = Intake staff, Registrar processor, Registrar approver, Release officer. Change = Registrar processor only. Everyone else is denied.

### Dashboard and look

- Dashboard with shortcut tiles (each shown only if the user may open it) and client totals by verification state (no names).
- BISU navy and gold theme taken from the official seal; rebuilt welcome and sign-in pages; lists turn into cards on phones; skip link and keyboard/accessibility fixes. The owner approved the look on 2 November 2026.

### Who can do what today

| Role | Can do now | Two-factor required |
|---|---|---|
| System administrator | Accounts, roles, audit trail (no client records) | Yes |
| Intake staff | View client records | No |
| Registrar processor | View and change client records | No |
| Registrar approver | View client records | Yes |
| Release officer | View client records | Yes |
| Cashier | Sign in only (Cashier screens not built) | Yes |
| Office supervisor (Registrar or Cashier) | Own office's audit events | Yes |
| Report exporter | Sign in only (reports not built) | Yes |
| Supplier staff / Supplier supervisor | Sign in only (Part 16 not built) | Supervisor only |
| Client (no role) | Own profile and settings | No |

### Behind the scenes (developer tools)

- One-command setup (`setup.ps1`) and full check (`check.ps1`).
- Test accounts, one per role, created by `DevelopmentAccountsSeeder`; their sign-in details go to `src/Credentials.md`, which is git-ignored. 20 obviously fake demo clients ("(Demo)", IDs starting with `DEMO`).
- Mailpit captures all email locally (no real mail is sent anywhere).
- Saved browser test suite: 34 cases × 4 viewport/theme combinations = 136 checks, in an isolated environment with its own fake data.
- Phone preview on the same Wi-Fi (`start-lan.cmd`), for testing on a real phone.
- Git hooks that refuse commits and pushes crediting AI tools as authors or co-authors; commits are local unless the owner asks to publish.

## 5. Ongoing right now

| Item | State | What is needed |
|---|---|---|
| **DEV-9 UI refresh** | Merged into `main` through PR #12 on 1 Nov 2026 (merge `a273f3d`); owner approved the look | — |
| **DEV-10 development two-factor email** | Built and tested; committed and pushed on branch `feat/dev-two-factor-email-2026-11-01` (C23) | Pull request and merge into `main` when the owner asks |
| **Part 05 services and fees** | Design ready ([Part 05](System_Plan/05-services-requirements-and-fees.md)) | **Owner approves the seven proposals (C15)**, then DEV-6 builds the staff service catalog on demo data |
| Office answers | None received yet; the requirements checklist is ready to send or follow up; only page 115 of the Citizen's Charter has been seen | Registrar and Cashier answers; the rest of the charter |
| Baseline observation (OF-4) | Kit ready; not started | Adviser agreement, Data Protection Officer informed, office heads' signatures |
| Local development stack | Stopped (Docker Desktop not running on 2 Nov) | Start Docker Desktop, then `docker compose -f docker-compose.dev.yml up -d` |

## 6. Waiting on the offices (open decisions)

| Decision | Who | What is needed |
|---|---|---|
| D03 / D04 / D09 | Registrar, MIS, Data Protection Officer | How clients prove identity and link accounts; official ID formats; approved import source and reviewers; permitted fields; privacy, retention and access auditing |
| D05 / D07 | Registrar, Cashier | Approved services, requirements, fees and payment rules; the complete Citizen's Charter |
| D08 | Registrar | Document templates, fonts, signatories, numbering, release rules, printer acceptance |
| D10 / D11 / D13 / D20 | MIS and offices | Kiosk/display devices, campus mail relay or alternative, workload targets, supported devices and browsers |
| D02 / D14 | MIS, sponsor, offices | Campus servers, staging and release owners, deployment approval |

Full register: [System_Plan/90-decisions-and-open-questions.md](System_Plan/90-decisions-and-open-questions.md).

## 7. Quality checks (latest)

- Full check on 1 Nov 2026 (including DEV-10): **180 tests / 1,105 assertions passed**, plus build, lint, TypeScript, Pint and Larastan.
- Saved browser suite on 1 Nov 2026 (DEV-9): **136 checks passed**.
- Client records on real MySQL: 45 tests / 394 assertions passed (18 Oct 2026).
- GitHub CI runs the foundation and browser jobs on every pull request; PR #11 and PR #12 passed and were merged.
- This is developer testing. No office user acceptance testing has happened yet.

### Known limitations

- Password-reset and verification emails only reach the local test inbox; campus mail delivery is undecided (D11).
- The demo ID format is a placeholder until the Registrar approves the real one (D04). Viewing a client record is not audited yet (D09).
- Input borders are below the WCAG 3:1 contrast guideline; detail-page tables still scroll sideways on phones.
- Building with self-registration turned off currently fails (a generated route is missing); needs a small fix before using that setting.

## 8. Folder structure

Repository root (everything is in Git except secrets, runtime data, dependencies and local test-account files):

```text
BISU-Registrar/
├── AGENTS.md                    Rules for developers and AI assistants working on the repo
├── README.md, CONTRIBUTING.md   Setup and contribution guide
├── Overall_System_Status.md     This file
├── SETUP-VALIDATION.md          Historical setup report
├── System_Plan/                 The plan: one file per part, plus tracker, handoff and decisions
│   ├── index.md                 Status tracker and navigation (start here)
│   ├── handoff.md               Current position, next steps, blockers
│   ├── 00-…16-*.md              Parts 00–16 (features, steps, acceptance criteria, implementation records)
│   ├── 90-decisions-and-open-questions.md   Confirmed decisions (C01–C23) and open office decisions (D01–D22)
│   ├── 91-requirement-traceability.md       Objectives → parts → checks
│   ├── 92-planning-change-log.md            Dated history of every change
│   ├── 93-plan-review.md, 94-team-schedule-and-risks.md, 95-office-interview-checklist.md
│   ├── 96-glossary.md, 97-work-while-waiting.md, 99-part-template.md
│   ├── project-concept-brief.md             Original concept brief
│   └── requirements.pdf/.html, baseline-kit.pdf/.html, baseline-templates/   Office checklist and observation kit
├── docs/                        Validation and design reports per slice (DEV-2…DEV-10, Part 03/04 QA, release report, phone preview)
├── experiments/pdf-renderers/   Isolated PDF tool comparison (not part of the app)
├── scripts/                     Browser-suite helpers and brand icon builder
├── docker/php.ini, Dockerfile.dev
├── docker-compose.dev.yml       Development stack (app, Vite, MySQL, Redis, Mailpit, worker, scheduler)
├── docker-compose.browser.yml   Isolated browser-test stack
├── docker-compose.lan.yml       Optional phone preview on the same Wi-Fi
├── docker-compose.pdf-spike.yml PDF experiment
├── setup.ps1, check.ps1, browser-tests.ps1, lan-preview.ps1, start-lan.cmd, pdf-spike.ps1, install-git-hooks.ps1
├── .githooks/                   Commit/push guards against AI/tool author or co-author credits
├── .github/                     CI workflow (checks.yml), Dependabot, PR template
└── src/                         The Laravel application
```

Application (`src/`):

```text
src/
├── app/
│   ├── Actions/
│   │   ├── Accounts/            Provision staff, assign/revoke role, deactivate/reactivate, temporary password, reset 2FA, …
│   │   ├── Clients/             Create/correct client, add/retire ID, verify, flag
│   │   └── Fortify/             Registration and password reset
│   ├── Console/Commands/CreateFirstAdmin.php    bisu:create-admin
│   ├── Enums/                   Role, Permission, OfficeCode, AuditAction, AuditOutcome, ClientCategory,
│   │                            IdentifierType, VerificationState, ClientRevisionAction
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           Users, roles, status, credentials, audit trail
│   │   │   ├── Registrar/       Client profiles, identifiers, verification
│   │   │   ├── Settings/        Profile, security
│   │   │   ├── Auth/            Forced password change; DevelopmentTwoFactorCodeController (DEV-10, not merged yet)
│   │   │   └── DashboardController.php
│   │   ├── Middleware/          Account active, idle timeout, forced password change, two-factor for sensitive roles,
│   │   │                        audit of refused access, correlation id, Inertia
│   │   └── Requests/            Form validation
│   ├── Models/                  User, UserRole, Office, AuditEvent, ClientProfile, ClientIdentifier, ClientProfileRevision
│   ├── Notifications/           DevelopmentTwoFactorCode (DEV-10, not merged yet)
│   ├── Policies/                UserPolicy, ClientProfilePolicy, AuditEventPolicy (office-scoped)
│   ├── Providers/               App and Fortify setup (sign-in rules, views, rate limits)
│   └── Support/                 Accounts (temporary passwords, sessions, admin guard, dev 2FA email),
│                                Audit (recorder, correlation id), Clients (recorder, ID normalizer, ID mask)
├── config/                      Laravel config; bisu.php holds project settings (registration, idle minutes, temp-password hours, dev 2FA email)
├── database/
│   ├── migrations/              Users, cache, jobs, passkeys, two-factor columns, offices, account status,
│   │                            user_roles, audit_events, client_profiles, client_identifiers, client_profile_revisions
│   ├── factories/
│   └── seeders/                 DevelopmentAccountsSeeder, DevelopmentClientsSeeder, BrowserTestSeeder (isolated tests only)
├── resources/
│   ├── css/app.css              Theme colour tokens (navy/gold, light/dark)
│   └── js/
│       ├── pages/               Welcome, Dashboard, auth/*, settings/*, admin/users/*, admin/audit, registrar/clients/*
│       ├── components/          App shell, PageHeader, EmptyState, two-factor and passkey components, admin/ and clients/ dialogs,
│       │                        ui/ (shadcn-vue: button, dialog, table, input-otp, sidebar, …)
│       ├── layouts/             App (sidebar/header), auth, settings
│       ├── composables/, lib/, types/
│       └── routes/, actions/, wayfinder/   Generated by Wayfinder at build time (git-ignored)
├── routes/                      web.php, settings.php, admin.php, registrar.php, development.php (DEV-10, not merged yet)
└── tests/
    ├── Feature/                 Accounts, Admin, Audit, Auth, Registrar, Settings, Dashboard
    ├── Unit/                    Role/permission matrix, PHP↔TypeScript permission sync, ID rule
    └── Browser/                 Playwright client-record suite
```

The 40 permissions already defined cover future parts too (services, fees, requests, queue, payments, documents, releases, reports, suppliers), but only account, audit and client-record permissions are used by screens today.

## 9. How work is done on this project

- Read `System_Plan/index.md` and the affected part first; confirm against the decision register.
- One slice at a time: design → owner approval (C-number) → local branch → build with tests → `./check.ps1` → validation report in `docs/` → handoff rewrite and change-log entry.
- Server-side validation and authorization on every action; office-scoped policies; hiding a button is never security.
- Additive migrations; synthetic data only; no secrets, real records or runtime data in Git.
- Commits are local; pushing, pull requests and merges only on the owner's explicit request. Commit authors are real people only.

## 10. Next steps

1. Owner approves, changes or rejects the Part 05 design (C15). On approval, build DEV-6: the staff service catalog with demo services and fees.
2. Open a pull request for the DEV-10 branch and merge it when the owner asks.
3. Owner works on office tasks: requirements checklist follow-up, rest of the Citizen's Charter, baseline observation approvals.
4. Alternative or parallel: write the basic queue design (DEV-7, Part 07).

## Where to read more

- [System_Plan/index.md](System_Plan/index.md) — status by part
- [System_Plan/handoff.md](System_Plan/handoff.md) — current position and next actions
- [System_Plan/90-decisions-and-open-questions.md](System_Plan/90-decisions-and-open-questions.md) — decisions
- [System_Plan/97-work-while-waiting.md](System_Plan/97-work-while-waiting.md) — work possible before office answers
- [docs/](docs/) — validation reports
