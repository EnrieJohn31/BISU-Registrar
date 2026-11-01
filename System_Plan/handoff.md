# Handoff

[Project index](index.md) | [Waiting-work list](97-work-while-waiting.md) | [Decisions](90-decisions-and-open-questions.md) | [Change log](92-planning-change-log.md)

## Current position — 2026-11-02

**DEV-10 (C22) is done: a development-only "Email me the code" button that sends the current two-factor code to Mailpit.** It is committed on its own branch `feat/dev-two-factor-email-2026-11-01`, started from `main` after PR #12, and pushed to GitHub at the owner's request (C23). No pull request or merge yet.

**DEV-9 UI refresh is merged into `main` through [PR #12](https://github.com/EnrieJohn31/BISU-Registrar/pull/12) (C19–C21, merge `a273f3d`, 2026-11-01).** The owner approved the look on 2026-11-02. A plain-language snapshot of the whole system, including the folder structure, is in [Overall_System_Status.md](../Overall_System_Status.md). The offices have not sent answers yet, so the owner chose UI and tester-convenience work for now. Part 05 still needs the owner's design approval (proposed C15) before any build. Campus deployment is still pending.

The owner reported the **phone test (OPS-1) done** on 2026-11-01. Phone model/OS/browser versions and whether a second laptop was checked were not recorded. Normal loopback development is running.

### Project status at a glance

| Area | Where it stands | What is next / still gated |
|---|---|---|
| Foundation / Part 03 accounts, roles and audit | Implemented; merged (`fa2246a`) | Institutional UAT and campus rollout pending |
| Part 04 slice 1 client records | Implemented, merged via PR #11 | Official ID/data/privacy/custodian acceptance remain D04/D09 |
| Part 04 imports and account linking | Design only | D03/D04/D09 and separately scoped feature work |
| DEV-2 worker, scheduler, Mailpit | Complete, merged | Campus mail/operations decisions still open |
| DEV-3 saved browser regression | Complete: 34 cases × four projects; passed again for DEV-9 | Extend when later features are scoped; physical device acceptance remains D20 |
| DEV-4 PDF renderer comparison | Complete; provisional Dompdf direction | Real templates/fonts/printer require D08 acceptance |
| DEV-8 BISU seal and browser icons | Complete, merged | — |
| DEV-9 UI refresh | Done; merged through PR #12 (C21, `a273f3d`). [Report and commit list](../docs/DEV-9-UI-REFRESH.md) | Owner approved the look 2026-11-02 |
| **DEV-10 development two-factor email** | **Done (C22); pushed on `feat/dev-two-factor-email-2026-11-01` (C23).** [Report](../docs/DEV-10-DEV-TWO-FACTOR-EMAIL.md) | Pull request and merge when the owner asks (CI runs on pull requests); optional browser case |
| OPS-1 same-network phone access | Done (owner-reported 2026-11-01) | Record device/browser versions and a second-laptop check under D20 when known |
| Part 05 services, requirements and fees | Slice 1 design written (DEV-5), not built | **You approve the design (proposed C15)**, then DEV-6 builds the staff catalog on synthetic data |
| Part 07 basic queue | Not implemented | DEV-7 needs its own short design and approval |
| Portal, Registrar and Cashier workflows; documents/release/reports | Not implemented | Office answers and each part's design/approval gates |
| Campus staging / production | Not deployed | MIS infrastructure, acceptance and release approval |

### Latest slice — DEV-10 development two-factor email (C22)

The owner had no authenticator app and asked for two-factor codes by email to Mailpit. Given the choice, the owner picked a development-only helper over email codes as a real second factor.

- **What it does:** the sign-in challenge and the setup dialog's verification step show a "Development only" box. **Email me the code** sends the account's current authenticator code to Mailpit (http://localhost:8026, PC only). The code changes every 30 seconds and works once.
- **Boundaries:** works only when `APP_ENV=local` and `BISU_DEV_TWO_FACTOR_EMAIL` is on; otherwise the endpoints return 404 and no box shows. TOTP stays the factor and C09 is unchanged. The routes are registered everywhere so Wayfinder output matches across environments. The setup route joined the two-factor enrollment allow-list. Both routes allow 6 requests a minute.
- **Validation:** `./check.ps1` passed 180 tests / 1,105 assertions (6 new). Live: an `admin@bisu.test` sign-in reached the challenge, the request returned 200 and Mailpit received the code email. The saved browser suite was not rerun.

Earlier the same day: a refused recovery code had already been used. The test accounts were reseeded (new `Credentials.md`), and the owner's own local account was given System administrator through `AssignRole`, which audits it.

### Next action

1. **Try it:** sign in with your own account. You will be sent to Settings → Security to set up two-factor. Click **Enable 2FA** → **Continue** → **Email me the code**, open http://localhost:8026, enter the code and **save the recovery codes it shows**.
2. **DEV-10 is pushed** on `feat/dev-two-factor-email-2026-11-01`. When you want it in `main`, ask for a pull request; GitHub CI then runs the full checks. Further UI changes start on a new branch from `main`; colours are all in `app.css`.
3. Docker Desktop was not running on 2026-11-02. Start it, then `docker compose -f docker-compose.dev.yml up -d`.
4. **You review the [Part 05 design](05-services-requirements-and-fees.md#slice-1-design-catalog-core-dev-5-2026-10-18)** and approve, change or reject the seven proposals (including `services.view`). On approval I record C15 and build DEV-6 on a new local branch.
5. **Start the baseline (OF-4):** agree the method with the adviser, inform the Data Protection Officer and get each office head's signature (page 2 of the [kit](baseline-kit.pdf)) before observing.
6. **Send or follow up the [requirements checklist](requirements.pdf)**, starting with its "six to get first". The rest of the Citizen's Charter unlocks Part 05 slice 2.
7. **Alternative or parallel:** DEV-7 (basic queue) needs its own short design first. Thesis and office-preparation tasks in [97](97-work-while-waiting.md) need no code.
8. Optional follow-ups: a browser case for the DEV-10 box; stronger input borders (WCAG 3:1) and card layout for the remaining detail-page tables (DEV-9 report).

### Office decisions still needed

| Decisions | Owner | Still needed |
|---|---|---|
| D03 / D04 / D09 | Registrar + MIS + DPO | Linking/recovery proof, official IDs, approved import source/reviewer/correction authority, permitted fields, privacy/retention/access auditing |
| **C15 (proposed)** | Project owner | Approve, change or reject the seven Part 05 slice 1 proposals, including `services.view` |
| D05 / D07 | Registrar + Cashier | Approved services/requirements, fees, financial rules and complete charter source; the eight questions in the Part 05 design |
| D08 | Registrar | Approved templates/fonts, signatories, signatures, numbering, release rules and actual output/printer acceptance |
| D10 / D11 / D13 / D20 | MIS + offices | Kiosk/display devices and accessibility, campus mail/assisted delivery, workload targets, supported devices/browsers (record the OPS-1 phone details here) |
| D02 / D14 | MIS + sponsor + offices | Campus infrastructure, staging/release owners and deployment approval |

### Runtime and watch-outs

- Normal development: app http://localhost:8085, Vite http://localhost:5178, captured email http://localhost:8026; seven services. For phone access again, double-click `start-lan.cmd` (see [instructions](../docs/LAN-PHONE-PREVIEW.md)) and finish with `-Stop`. Remove any preview firewall rule you added. The Mailpit link does not open from a phone.
- Test accounts were reseeded on 2026-11-01; `src/Credentials.md` (git-ignored) is current. Each recovery code works once. Rerun `DevelopmentAccountsSeeder` to reset them; it does not touch other accounts.
- Two sessions worked in this checkout on 2026-11-01. Check `git status` and the branch before committing, and commit only the files of one slice. The merged `feat/ui-refresh-2026-11-01` branch is finished; do not add to it.
- Tests that switch to `APP_ENV=local` must turn off `PreventRequestForgery`: Laravel skips CSRF only in the testing environment.
- The isolated browser suite downloads Composer/npm packages on every run. A slow connection made one attempt time out on 2026-11-01 (GitHub download, not a test failure); rerun when the network is stable.
- Keep real records, private documents, secrets, runtime data and backups out of Git. Preserve human commit authorship and the attribution/push guards. Commit locally by default; C20 and C21 covered the DEV-9 branch and PR #12 only, and C23 the DEV-10 branch push only. Any further push, pull request or merge needs an explicit request.
- Builds with registration disabled still fail because `Register.vue` (and the welcome/login pages, which now hide the links but still import the route) import a route that generation omits. Record a separate technical fix before using that setting.
- New pages should use `PageHeader`, the semantic colour tokens (not raw hex or palette classes) and `.stack-table` with `data-label` cells for lists. Dashboard tiles must follow the target route's policy, not the shared permission list, because that list is not office-aware.
- For Part 05: enforce the office in the policy, name composite indexes explicitly (MySQL 64-character limit), compare JSON in tests with `assertEquals`, and keep PHP and TypeScript permission lists in step.
- Dompdf needs print-specific CSS; real templates, printer behaviour, private storage and queued retries need their own design/acceptance (DEV-4 report).
