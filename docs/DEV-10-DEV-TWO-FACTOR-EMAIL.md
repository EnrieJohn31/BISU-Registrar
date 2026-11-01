# DEV-10 — Development-only two-factor code by email

[Handoff](../System_Plan/handoff.md) | [Decision C22](../System_Plan/90-decisions-and-open-questions.md) | [Part 03](../System_Plan/03-accounts-permissions-and-audit.md)

## Why

Staff with sensitive roles must use two-factor authentication with an authenticator app (C09). The owner and testers had no authenticator app, and the campus has no mail relay yet (D11). The owner chose a development-only helper over email codes as a real second factor (C22).

## What it does

On a local development stack, two screens show a "Development only" box with **Email me the code**:

- the sign-in two-factor challenge (after a correct password), and
- the verification step of the two-factor setup dialog in Settings → Security.

The button emails the account's **current authenticator code** to Mailpit at http://localhost:8026. The code is the same one an authenticator app would show, so it changes every 30 seconds and works once. The inbox link opens only on the PC running Docker; a phone using the LAN preview cannot open it.

## Boundaries

- It works only when `APP_ENV=local` and `BISU_DEV_TWO_FACTOR_EMAIL` is not `false`. Anywhere else, the endpoints return 404 and the pages show no box.
- TOTP stays the second factor. Nothing changes how codes are checked, who must use two-factor, recovery codes or the audit trail.
- The routes are registered in every environment so the generated Wayfinder files match between local, CI and campus builds; the controller refuses them outside local.
- The challenge endpoint only serves the account that already passed the password step (Fortify's pending sign-in). The setup endpoint only serves a signed-in user with a new, unconfirmed secret, after password confirmation. Both are limited to 6 requests a minute.
- Mail is sent immediately, not queued, so the code arrives before it changes.

## Files

| File | Change |
|---|---|
| `src/config/bisu.php` | `dev_two_factor_email` flag (default on) and `dev_mail_inbox_url` (default `http://localhost:8026`) |
| `src/app/Support/Accounts/DevelopmentTwoFactorEmail.php` | On/off check, inbox link and sending the current code |
| `src/app/Notifications/DevelopmentTwoFactorCode.php` | The email |
| `src/app/Http/Controllers/Auth/DevelopmentTwoFactorCodeController.php`, `src/routes/development.php` | `dev.two-factor-code.challenge` and `dev.two-factor-code.setup` |
| `src/app/Http/Middleware/RequireTwoFactorForSensitiveRoles.php` | Setup route added to the enrollment allow-list |
| `src/app/Providers/FortifyServiceProvider.php`, `src/app/Http/Controllers/Settings/SecurityController.php` | Pass `devTwoFactorInboxUrl` (null when off) |
| `src/resources/js/components/DevTwoFactorCodeEmail.vue` and the challenge page, setup dialog, `ManageTwoFactor` and Security page | The button, sent/failed status and inbox link |
| `src/tests/Feature/Auth/DevelopmentTwoFactorCodeTest.php` | Six tests |

To remove it later, delete the new files, the `require` in `routes/web.php`, the allow-list entry, the two props and the component use.

## Validation (2026-11-01)

| Check | Result |
|---|---|
| `./check.ps1` | Passed: Composer validation, build with Wayfinder, lint/format, TypeScript, browser types, **180 tests / 1,105 assertions** (6 new) |
| New tests | Refused outside local; refused when the flag is off; refused without a pending sign-in; the emailed code completes a real sign-in; the emailed setup code confirms two-factor for a System administrator who had none (passes the enrollment middleware); refused once two-factor is confirmed; pages pass the inbox link only when on |
| Live stack | `admin@bisu.test` password sign-in → challenge page showed the inbox link → request returned `200 {"status":"sent"}` → Mailpit received "Your two-factor code (development)" for `admin@bisu.test`. The code itself was not printed |
| Not run | Saved browser suite (no browser case covers this box yet); visual review of the box in both themes |

## Notes

- Laravel skips its CSRF check only in the testing environment, so the tests that switch to `local` turn off `PreventRequestForgery` for those requests.
- Optional follow-up: a browser case for the box, and showing the Mailpit link in the `Credentials.md` header.
