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

## 2026-10-16 - Registrar vendor list and performance points (plan only)

- Project owner reported that the vendor side was missing from the plan; confirmed it was absent from all parts and from the original concept brief, so it is recorded as an owner addition (C07), not a brief requirement.
- Scoping answers: suppliers to the Registrar's Office; per-delivery weighted performance rating (criteria 1–5 → 0–100); staff-only; staff submit, supervisor confirms; low score flags For review with supervisor decision (no auto-suspension); reference numbers only, no money or Cashier link; compliance document type/expiry without uploads; Registrar-only, office-scoped; build after M2.
- Added [Part 16](16-vendor.md). Connected updates: index (Part 16 row, M3 scope), Part 02 entity row, Part 03 roles and audit events, Part 12 report line, Part 13 UAT-17–21 pointer, Part 15 navigation and future candidates, decision register (C07, D15–D17), traceability (C07).
- Validation: Markdown-only; links checked. No application code changed; no tests rerun.
- Next action: Registrar to decide D15 (criteria, weights, threshold, period), D16 (vendor fields, documents) and D17 (procurement relationship).

## 2026-10-17 - Whole-plan review, gap fixes and diagrams (plan only)

- Project owner asked for an overall check of the plan, updates for missed items, and illustrations. Findings, severities and a team check-off list are in [93-plan-review.md](93-plan-review.md).
- Added supporting files: [94](94-team-schedule-and-risks.md) (roles, iterations, decision gates, thesis checkpoints, Definition of Done, risk register), [95](95-office-interview-checklist.md) (questions per office mapped to decisions, documents to request) and [96](96-glossary.md) (glossary).
- New proposed content in Parts 01–16: Citizen's Charter as the service and processing-time source, client feedback (CSM), legally required priority lanes, privacy program items (DPO, PIA, notice versions, breach contacts), service calendar, stalled-request and unclaimed-document rules, office clearances as requirements, throttling and staff 2FA/idle timeout, browser/device baseline, security checklist, thesis evaluation and demo data, go-live cutover with parallel run, NTP, repository custody, new roadmap candidates.
- Decision register: D18 (stalled/unclaimed), D19 (feedback), D20 (browser/device baseline), D21 (repository custody); D02, D05, D06 and D09 widened; new proposed defaults. Traceability: nonfunctional N01–N08 and review additions A01–A07.
- Added 27 Mermaid diagrams across the index, Parts 01–16 and files 90, 93, 94, 95.
- Validation: relative links checked; every Mermaid diagram test-rendered with Mermaid in headless Edge (results in the session report). No application code changed; no tests rerun.
- Next action: team checks off 93; assigns roles and dates in 94; takes 95 to the offices; names an owner for each open decision.

## 2026-10-17 - Publish UI and system-plan updates

- Published the BISU welcome/login branding and shared authentication layout together with the expanded vendor, workflow, readiness, interview, schedule/risk and glossary plans.
- Validation: check.ps1 passed (Composer validation, asset/route generation, frontend formatting/lint/types, PHP formatting/static analysis, 40 tests / 138 assertions); 303 relative links across 27 plan files resolve.
- Used a feature branch and pull request for protected-main review. No campus deployment or business-module implementation is claimed.
