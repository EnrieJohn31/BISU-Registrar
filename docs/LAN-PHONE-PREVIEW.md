# Same-network phone preview

This is OPS-1 development preparation, not campus deployment. Use a trusted Wi-Fi network and development data. The default Compose setup remains loopback-only. The optional LAN override adds only app port 8085 on the private IPv4 address you select. Mailpit, Vite, MySQL and Redis keep their existing bindings; Vite is stopped while the phone uses compiled assets. Debug error pages are disabled during the preview.

Find your PC's Wi-Fi IPv4 address with `ipconfig`, then run from the repository root (replace the example address):

```powershell
./lan-preview.ps1 -Address 192.168.1.20
```

Connect the phone to the same Wi-Fi, keep the PC/Docker running, then open `http://192.168.1.20:8085` using the address you supplied. Sign in again: the PC's localhost session does not transfer to the phone. The script checks that the address is private and belongs to an active PC adapter. It builds assets, removes a stale Vite hot marker, and recreates app/worker/scheduler with the LAN `APP_URL` and debug disabled. The source `.env`, application key, records, roles and business settings are preserved. New verification links use the LAN URL; old signed links keep their original hostname. Mailpit stays available only on the PC at http://localhost:8026, so complete verification on the PC first if needed.

If the phone cannot connect, allow TCP 8085 from the local subnet in **administrator PowerShell**, substituting the same PC address and adapter name (`Wi-Fi` on this development machine):

```powershell
New-NetFirewallRule -Name BISU-Registrar-Dev-LAN -DisplayName 'BISU Registrar temporary LAN preview' -Direction Inbound -Action Allow -Protocol TCP -LocalPort 8085 -LocalAddress 192.168.1.20 -RemoteAddress LocalSubnet -InterfaceAlias 'Wi-Fi' -Profile Any
```

This rule is limited to the chosen local address, adapter, port and local subnet. It works with the existing Windows network category; changing the network category, disabling the firewall or configuring router forwarding is unnecessary. The script does not change firewall rules. If that named rule already exists for an old address, remove it using the command below before creating it again. If it still fails, check that the phone uses Wi-Fi rather than mobile data, VPN routing, and guest Wi-Fi/client isolation. An address changed by DHCP requires restarting the preview and updating the rule.

Finish the preview and restore ordinary development:

```powershell
./lan-preview.ps1 -Stop
# Administrator PowerShell, only if you created the preview rule:
Remove-NetFirewallRule -Name BISU-Registrar-Dev-LAN
```

The stop action recreates app/worker/scheduler with base settings and starts Vite. No database volume is removed. Do not run `setup.ps1`, `check.ps1` or base `docker compose ... up` during the preview: they can restore base containers or the localhost hot marker. Finish the preview first; rerun the start command after source changes to rebuild. Future optimization, if frequent device testing warrants it: design a separate preview service/image to avoid pausing Vite.

Record the actual phone model, OS/browser version, page behavior and remaining issues in the handoff after testing. PC HTTP/asset checks and phone emulation do not prove physical Wi-Fi reachability. Part 05 still awaits design approval; no office gate is changed.

Use password/TOTP for this HTTP preview. Passkeys on the phone require trusted HTTPS because [WebAuthn requires a secure context](https://developer.mozilla.org/en-US/docs/Web/API/Web_Authentication_API).

## Validation — 2026-10-18

- PowerShell syntax and merged Compose validated on Compose 5.6.0. Resolved ports add only the selected app IPv4 binding; app/worker/scheduler share the LAN URL with debug disabled. Database/cache have no published ports; Vite and Mailpit retain loopback bindings.
- Production asset build/Wayfinder passed. LAN and localhost login requests each returned HTTP 200; all 28 referenced compiled assets returned HTTP 200, with no Vite/HMR reference. The source `.env` SHA-256 was identical before and after startup.
- Stop action verified: app returned to loopback-only binding and all seven base services were running. Preview was started again for the owner's phone test; six services run with Vite stopped.
- Final runtime checks confirmed the LAN URL and `debug=false` inside app, worker and scheduler, both intended app bindings and both login responses. All 152 local Markdown links across eight changed documents resolved; `git diff --check` passed.
- No application source/package/database/permission change. Full foundation/browser regression was not repeated for this root-only slice. Earlier 167 tests / 944 assertions and 136 browser checks remain historical evidence.
- Physical phone/second-laptop connectivity and browser versions remain pending. Windows administrator rights are unavailable to the agent, so no firewall rule was installed; the exact scoped administrator command above is available if needed.

The override requires Compose 2.24.4+ and uses [`!override` to replace the port list](https://docs.docker.com/reference/compose-file/merge/#replace-value). Compiled assets follow [Laravel's Vite build mode](https://laravel.com/framework/docs/13.x/vite#running-vite). The firewall command uses Microsoft's [`New-NetFirewallRule`](https://learn.microsoft.com/en-us/powershell/module/netsecurity/new-netfirewallrule).
