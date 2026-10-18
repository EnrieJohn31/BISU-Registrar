# Same-network phone preview

This is OPS-1 development preparation, not campus deployment. Use a trusted Wi-Fi network and development data. The default Compose setup remains loopback-only. The optional LAN override adds only app port 8085 on the private IPv4 address detected or selected. Mailpit, Vite, MySQL and Redis keep their existing bindings; Vite is stopped while the phone uses compiled assets. Debug error pages are disabled during the preview.

For daily startup, start Docker Desktop, then **double-click `start-lan.cmd`** in the repository folder. Its window stays open with the connection links or an error. Alternatively run from the repository root:

```powershell
./lan-preview.ps1
# Show the running app's links again without restarting or rebuilding:
./lan-preview.ps1 -Links
```

Startup detects a private IPv4 address on an active adapter with an IPv4 gateway. Virtual adapters without a gateway are excluded from automatic selection. If more than one address qualifies (for example Wi-Fi plus Ethernet or a VPN), it stops before changing containers and lists your choices. Select the intended LAN explicitly with `./lan-preview.ps1 -Address <PC-IPv4-address>`. Explicit addresses must still be private and assigned to an active PC adapter.

The completed startup prints the actual running app's PC link, phone link and PC-only Mailpit link, along with the chosen IP and adapter. `-Links` reads current Docker bindings without rebuilding, restarting or changing configuration; it avoids presenting a phone link when that bound address is no longer active. If phone access is off or the address changed, run startup again.

Connect the phone to the same Wi-Fi/local network, keep the PC/Docker running, then open the **Phone / same LAN** link printed in the window. Sign in again: the PC's localhost session does not transfer to the phone. The script builds assets, removes a stale Vite hot marker, and recreates app/worker/scheduler with the LAN `APP_URL` and debug disabled. The source `.env`, application key, records, roles and business settings are preserved. New verification links use the LAN URL; old signed links keep their original hostname. Mailpit stays available only on the PC at http://localhost:8026, so complete verification on the PC first if needed.

This helper changes no router settings and reserves no permanent IP address. With [Windows automatic DHCP configuration](https://support.microsoft.com/en-us/windows/experience/connectivity-networking/essential-network-settings-and-tasks-in-windows), the router/access point assigns addressing. Rerunning startup detects the current address rather than assuming the previous one. Address-specific firewall rules and old signed email links still need refreshing if it changes.

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
- Owner-confirmed physical phone result (2026-10-18): “Login page opens” at http://192.168.1.8:8085 over the same Wi-Fi. Basic reachability passed; phone sign-in/navigation, model/OS/browser versions and second-laptop access remain pending. The agent installed no firewall rule; whether the owner added one was not reported. No runtime/account change was made for this evidence follow-up.

### Daily-startup follow-up — 2026-10-18

The Windows launcher passed an end-to-end invocation through the repository's full path (including spaces). No address was supplied: the helper selected `192.168.1.8` on Wi-Fi and excluded three gateway-less virtual adapters. Build/Wayfinder passed and the final console printed PC, phone and PC-only inbox links. The launcher also preserved a failed helper's nonzero exit code during validation. Its CRLF checkout setting is explicit in `.gitattributes`.

`-Links` printed actual running bindings and left container IDs and the source `.env` hash unchanged. A first startup check exposed PowerShell's array `Address` method shadowing member enumeration; address validation now explicitly enumerates the address strings. Final syntax checks passed; both login URLs and all 28 compiled asset references per page returned HTTP 200 without HMR. Six preview services remain running, with Vite paused and the environment unchanged. No application source, dependency, Compose binding definition, router or firewall changed. No additional physical phone run or full foundation/browser regression is claimed.

The override requires Compose 2.24.4+ and uses [`!override` to replace the port list](https://docs.docker.com/reference/compose-file/merge/#replace-value). Compiled assets follow [Laravel's Vite build mode](https://laravel.com/framework/docs/13.x/vite#running-vite). The firewall command uses Microsoft's [`New-NetFirewallRule`](https://learn.microsoft.com/en-us/powershell/module/netsecurity/new-netfirewallrule).
