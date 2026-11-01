# DEV-9 — UI refresh: BISU colours, public pages, phone lists and dashboard

[Baseline](../System_Plan/00-current-baseline.md) | [Waiting work](../System_Plan/97-work-while-waiting.md) | [Handoff](../System_Plan/handoff.md) | [DEV-8 branding](DEV-8-BISU-BRANDING.md)

## Scope and design — 2026-11-01

The offices have not answered yet, so the owner chose UI work for now (C19). Four parts were approved together, with the navy and gold colour direction:

1. BISU colour theme and rebuilt welcome/sign-in pages.
2. Phone-friendly client, user and audit lists.
3. A dashboard with shortcuts and client totals.
4. A consistency and accessibility pass.

Presentation only. No business rule, office policy, permission, migration or route name changes. C15 (Part 05) and every office gate stay in force. The colours are taken from the seal for the application. This is not an institutional brand guide.

### Colour theme

Sampled from the committed seal source (`src/resources/branding/bisu-seal-source.png`): navy `#000066` from the lettering and outer ring, gold `#FFCC00` from the ring band. The purple of the globe (`#300060` region) only tints the navy panels as a soft gradient.

| Token use | Light mode | Dark mode |
|---|---|---|
| Primary (buttons, links, checked controls) | Navy `hsl(240 100% 20%)` | Gold `hsl(48 100% 50%)` with navy text |
| Focus ring | Navy | Gold |
| Accent / hover, secondary, muted, borders | Navy-tinted greys | Navy-tinted dark surfaces |
| Active menu item | Navy-tint background, 3px gold left marker | Dark navy background, gold marker |
| `bg-navy`, `bg-gold`, `text-gold-foreground` | Fixed brand colours for panels, the top stripe and the gold call-to-action | Same |

One deviation from the approved preview: the preview listed gold for the focus ring. Gold on white measures about 1.6:1, below the 3:1 that WCAG 1.4.11 needs for a focus indicator, so light mode uses a navy ring. Gold remains the ring in dark mode, where it measures 12.8:1. Destructive red was darkened in light mode (white text now 6.0:1; the starter's red was 3.8:1).

All tokens live in `src/resources/css/app.css` (`:root` and `.dark`); pages use the semantic names (`primary`, `muted-foreground`, …), so later colour changes stay in one file.

### Public pages

- **Welcome:** removes the starter's hard-coded hex colours. Seal and campus name in the header; a navy hero with the large seal, the project title and one call to action; three cards for what the system is for (document requests, queue tickets, payments), introduced as being built in stages and opening once the offices approve each service; a campus-network footer. Wording stays general: no fee, step or turnaround promise.
- **Sign-in layout (all Fortify pages):** the empty blue placeholder panel is now a navy panel with the large seal, "BISU Registrar", "Balilihan Campus" and a campus-network note. On phones, where the panel is hidden, a small seal and campus name sit above the form.
- **Login:** "Sign in" replaces "Welcome Back 👋"; visible "Email address" and "Password" labels replace screen-reader-only labels; positive `tabindex` values are removed so focus follows the page order. "Sign up" (login) and "Register" (welcome) appear only when Fortify registration is enabled: both pages now receive `canRegister`, read on each request.
- **Large seal:** `scripts/build-brand-icons.ps1` now also writes a 320px derivative (`public/branding/bisu-seal-large.png`, 138,299 bytes) for panels that show the seal at up to 160 CSS pixels. `AppLogoIcon` takes a `large` prop. Re-running the script reproduced every existing icon byte for byte; the source PNG is unchanged.

### Phone-friendly lists

A `.stack-table` wrapper (in `app.css`) turns each table row into a card below the `md` breakpoint (768px): header row visually hidden, first cell full width, other cells in two columns, each prefixed by its column name from `data-label`; `data-stack="full"` widens a cell. The markup stays a single table, so screen readers, keyboard focus on clickable audit rows and the saved browser tests all address the same rows on every screen. The rules are unlayered CSS so they outrank the table primitives' utility classes at phone width only. Desktop tables are unchanged apart from the colours.

Applied to client records, users and the audit trail. Long audit reasons are clamped to two lines on phones; the detail sheet still shows the full text.

### Dashboard

`GET /dashboard` now goes through an invokable `DashboardController` (same route name and middleware). It sends:

- `access`: which shortcuts to show, each decided by the **same policy** that guards the target route (`ClientProfilePolicy`, `UserPolicy`, `AuditEventPolicy`). The shared permission list is not office-aware, so these server checks keep a tile from linking to a page that would be refused (for example a Cashier-office assignment that lists a client permission).
- `clientCounts` (only when `viewAny` client records is allowed): totals by verification state and the number updated in the last 7 days. One grouped count query plus one count; no names, IDs or rows are sent.

The page shows the date, the viewer's role badges, four count tiles that open the client list with the matching verification filter (the "Needs review" tile turns amber when it is above zero), and shortcut cards (client records, new client, users, create staff account, audit trail, security settings) according to access. Each shortcut card is clickable as a whole while the link's accessible name stays its title. Client accounts keep the "Link your Student or Alumni ID" card.

### Consistency and accessibility

- New `PageHeader` component: one `h1` per workspace page, description, a details slot (badges) and an actions slot. Replaces the per-page header blocks and the `-mt-6` spacing workaround on the client and user detail pages. `Heading` remains for settings sections.
- New `EmptyState` component used in the three lists: icon, the existing message and a hint (check spelling, clear a filter, widen the date range).
- Skip-to-content link as the first focusable element of the signed-in layout; it moves focus to `<main id="main-content">`.
- The show/hide password button is now reachable by keyboard (it had `tabindex="-1"`).

## Connections

- Routes: `dashboard` keeps its name, URI and `auth`/`verified` middleware, so generated `dashboard()` links are unchanged; Wayfinder (git-ignored output) also generates the new controller action. `home` became a closure route so `canRegister` is read per request; route caching already handles the existing closure route in `settings.php`.
- Authorization: no policy or permission changed. The dashboard reuses the existing policies; the server still checks every request behind each tile.
- Registration: the setting itself (`BISU_REGISTRATION_ENABLED`) is unchanged. Hiding the links does not fix the known build failure when registration is off (`Register.vue` and these two pages import a route that generation omits); that separate technical fix is still listed in the handoff.
- Part 03/04 behaviour, audit records, client privacy rules and saved browser cases are unchanged. Settings pages and dialogs pick up the theme through tokens only.

## Validation

- `./check.ps1`: **174 tests / 1,055 assertions passed** (was 167 / 944), with Composer validation, production build/Wayfinder, formatting/lint, Vue and browser TypeScript, Pint and Larastan level 7. Worker, scheduler and Vite were restored afterwards.
- New feature tests: dashboard shortcuts and totals for a client account, a Registrar processor (counts by state, 7-day window excludes an 8-day-old update), view-only Intake (no create shortcut), Cashier and Cashier-office supervisor (no client data) and a system administrator (account and audit shortcuts only); `canRegister` on the welcome and login pages flips with the Fortify setting.
- `./browser-tests.ps1 -SkipBuild`: **all 136 saved regression checks passed** (34 cases × desktop/phone × light/dark) with zero retries, on isolated synthetic fixtures. The cases that select rows by `tbody tr`, table roles and exact link names (B01–B13, B27, B30, B32, B33) passed at phone width with the card layout.
- A temporary review spec ran in the same isolated project (20 checks across the four projects, not committed) and captured welcome, sign-in, dashboards for processor/administrator/Cashier, client list (all, filtered, empty), users, audit trail with detail sheet and filters, the phone menu and the focused skip link. It also asserted no page-level horizontal overflow on each list, the Needs-review tile opening the filtered list, the skip link moving focus to `<main>`, and the login tab order (email → password → show password → remember me → forgot password → sign in). The final run produced 56 captures (14 screens × 4 projects); 17 were inspected directly, covering welcome, sign-in, processor dashboard, client list and empty state, users, audit list/detail/filters, the phone menu and the skip link across desktop/phone and light/dark. The administrator and Cashier dashboards were checked by assertions only. The review led to four fixes in this slice (skip-link padding, "Overview" sidebar label, initials ignoring "(Demo)", visible From/To labels on the audit date filters). Captures stay in ignored `.browser-artifacts/ui-review/`.
- Colour contrast was computed from the `app.css` tokens: every text pair (foreground, muted, primary, secondary, accent, sidebar, gold call-to-action, navy panel text, destructive button) meets WCAG AA 4.5:1 in both themes; focus rings measure 17.6:1 (light) and 12.8:1 (dark).
- One earlier browser run failed before any test because a GitHub package download timed out during setup (63 KB in 300 s); a direct download minutes later took 12 s and the reruns completed. No test failure was involved.

## Commits

Branch `feat/ui-refresh-2026-11-01`, pushed to GitHub at the owner's request (C20); no pull request or merge. Each commit message lists what it changed.

| Commit | What it changed |
|---|---|
| `cc5a1ac` style: BISU navy and gold theme from the seal | Colour tokens in `app.css` for light and dark mode; `navy`/`gold` tokens; navy focus ring in light mode; darker destructive red; gold active marker and "Overview" label in the sidebar |
| `87a8bf6` feat: rebuild welcome and sign-in pages around the seal | Welcome page; sign-in panel and phone header; login wording, labels and tab order; keyboard-reachable show-password; `canRegister` on welcome (closure route) and login; 320px seal derivative and `AppLogoIcon large`; registration-link tests and `disableFortifyFeature` helper |
| `2f6213d` feat: shared page header, skip link and accessibility fixes | `PageHeader` and `EmptyState` components; client/user detail and create pages use `PageHeader`; skip-to-content link; initials ignore "(Demo)" |
| `c4884c4` feat: phone-friendly client, user and audit lists | `.stack-table` card layout CSS; the three lists use it with `PageHeader` and `EmptyState`; audit reason clamp and visible From/To labels |
| `8344d66` feat: dashboard shortcuts and client totals | `DashboardController`; policy-based shortcut tiles; client totals by verification state with filtered links; dashboard tests |
| docs commit | Decisions C19/C20, this report, OPS-1 phone-test record, waiting-work list, index, change log and handoff |

The single tested commit was split into these commits without changing the code: the final tree under `src/` and `scripts/` is identical to the one that passed `./check.ps1` and the browser suite. Intermediate commits were not tested one by one.

## Remaining limits and useful optimization

- Input borders measure 1.54:1 (light) and 1.68:1 (dark): stronger than the starter's, but below the 3:1 that WCAG 1.4.11 asks for component boundaries. The fields now have visible labels; a darker border would change the look of every form, so it is left for the owner to decide.
- The theme has not been reviewed with the offices or on campus projectors and kiosk screens (D10/D20). Adjust tokens in `app.css` if a display washes out the navy or gold.
- The dashboard counts run on each visit: one grouped count and one count on `client_profiles` (`updated_at` is not indexed). Add an index or a short cache only if the table grows into the tens of thousands.
- Other tables (client IDs and history on the detail page, user roles) still scroll sideways on phones where needed; apply `.stack-table` when those screens are next changed.
