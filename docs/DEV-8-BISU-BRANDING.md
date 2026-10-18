# DEV-8 — BISU branding and stray-file cleanup

[Baseline](../System_Plan/00-current-baseline.md) | [Waiting work](../System_Plan/97-work-while-waiting.md) | [Handoff](../System_Plan/handoff.md)

## Scope and design — 2026-10-18

The owner authorized DEV-8 as the next eligible waiting-work item. Replace the starter sidebar/shared mark and browser icons using BISU's existing seal, remove only the known untracked `src/nul` diagnostic file and preserve authentication, generated routes and office rules. C11's waiting-work boundary remains in force.

No official image was present in the repository. The [university website](https://bisu.edu.ph/) links the [Balilihan campus website](https://bisubalilihan.com/), whose public media library supplies the [full-size seal](https://bisubalilihan.com/wp-content/uploads/2021/03/BISU-LOGO-1.png) (media ID 9045). Its smaller header seal was visually compared. Preserve the retrieved PNG unchanged under `src/resources/branding/`; create only proportion-preserving size/format derivatives. No invented lettering, recoloring or replacement mark.

- Source: 1600 × 1600 transparent PNG, 411,527 bytes. The campus API's nominal media size is 2048 × 2048; the actual downloaded file is 1600 × 1600.
- Original SHA-256: `b60f3141d6efeaab798c26c05f8db836e1130a969a9cf7bb751257e239833442`.
- Shared display asset: 96 × 96 PNG, rendered at 32 CSS pixels in the sidebar, supporting dense screens without transferring the full source.
- Browser assets: 64 × 64 PNG favicon; ICO containing 16/32/48-pixel PNG frames; 180 × 180 touch icon.
- Generated assets are committed. `scripts/build-brand-icons.ps1` uses Windows `System.Drawing` for deterministic resizing/encoding; no image library or app dependency is added. Source provenance and the regeneration command live beside the source image.

## Connections

`AppLogoIcon.vue` keeps its existing prop/attribute forwarding but renders the local seal with intrinsic dimensions and an accessible name. `AppLogo.vue` uses the full 32-pixel square and removes the starter's themed icon tile. The app name and generated dashboard link remain intact. Header and alternative authentication layouts remove monochrome SVG-only styles; the active split authentication layout's content remains unchanged.

The common Blade shell serves new local PNG/ICO/touch references on public, authentication and staff screens. A version query changes the cache key for existing icon filenames. The obsolete starter `favicon.svg` is removed. All assets load from the app origin; there is no BISU/CDN request at runtime.

`src/nul` was untracked diagnostic text (1,916 characters, 42 line breaks), with no application references. Only that exact path was removed after checking its parent inside this workspace, using the Windows extended path required for a reserved filename. Root `.gitignore` now excludes `/src/nul`. No runtime records, credentials, database or other file was removed.

## Validation

- `powershell -NoProfile -ExecutionPolicy Bypass -File ./browser-tests.ps1 -SkipBuild`: **136 checks passed**, 34 cases × desktop/phone emulation × light/dark, zero retries. This reran the existing suite; no permanent branding tests or business fixtures were added.
- A separate one-off inspection in the disposable browser project verified the 32 × 32 displayed seal (96 × 96 source) fits its generated `/dashboard` link in expanded/collapsed desktop navigation and phone drawers in both themes. Six captures were visually reviewed. Public home/login pages expose the same three local browser-icon links; icon HTTP responses match the committed files byte for byte. No page exception or external request was observed during that navigation inspection.
- Captures and inspection details stay in ignored `.browser-artifacts/branding/`; the full suite report is `.browser-artifacts/report/index.html` with JSON at `results.json`. All shown accounts/data are synthetic. The temporary inspection script is excluded from Git.
- All generated PNGs decode at their declared dimensions and retain transparent corners; all three ICO PNG frames decode at 16/32/48 pixels. A second generator run produced identical SHA-256 hashes for every output. PowerShell syntax and `git diff --check` passed.
- The development `.env` SHA-256 and read-only user/profile/revision/audit counts plus profile-version sum matched before and after the browser run. Its workspace, containers and network were removed. The existing development data was not reset or edited.
- `powershell -NoProfile -ExecutionPolicy Bypass -File ./check.ps1`: **167 tests / 944 assertions passed**, Composer validation, production build/Wayfinder, frontend formatting/lint, Vue/browser TypeScript, Pint and Larastan level 7. Vite/worker/scheduler were restored afterwards. The initial check requested formatting in four shortened Vue snippets; the project formatter corrected whitespace only, with no extra source changes.
- Final housekeeping: all seven development services running; isolated browser containers/network/workspace absent. Development `/login` and all four seal/icon URLs returned HTTP 200. Resolved 138 relative Markdown links in nine changed documents. Git whitespace checks pass; the exact stray path is absent and ignored. No push, GitHub execution or campus deployment.

## Remaining limits and useful optimization

This installs the campus-hosted university seal for development; it does not approve an institutional brand guide or campus deployment. Small favicons identify the seal's shape/colors rather than making its lettering readable. If MIS later supplies a dedicated small-size official icon, swap the source through the same documented workflow.

Keep the full source out of the public asset directory and reuse the 96-pixel derivative across layouts. Preserve local assets and system fonts for campus-LAN operation. The next waiting-work item is DEV-4's PDF comparison on a fictional certificate stamped “SAMPLE – NOT VALID”; real template wording, signatories and numbering remain D08-gated. Part 05 implementation still requires its design approval.
