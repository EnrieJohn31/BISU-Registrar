# Part 14 — BISU local-network deployment and operations

[Back to index](index.md) | [Previous](13-testing-and-campus-acceptance.md) | [Next](15-training-handover-and-roadmap.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft; campus LAN hosting confirmed |
| Implementation | Development Docker exists; operational deployment not started |
| Validation | Production/restore/rollback checks not started |
| Deployment | No staging or campus-production deployment recorded |
| Owner | Campus MIS/IT deployment owner with office sponsors; names pending |
| Dependencies | Parts 03–12, Part 13 acceptance, Part 15 handover readiness |
| Updated | 2026-10-16 |

## Confirmed deployment model

Host BISU Registrar on BISU-managed local infrastructure and expose it to approved campus LAN/Wi-Fi devices. “Online” means campus browser access. Public Internet hosting, public DNS exposure and off-campus access are outside the initial scope.

Development at `localhost:8085` is accessible only on the developer machine under the current Compose binding. It is not campus deployment. A campus system used for real transactions is **production**, even though it is private and local-network-only.

## Proposed topology

```mermaid
flowchart LR
    C[Campus clients / office PCs / kiosk / display] --> T[Internal hostname + trusted HTTPS]
    T --> P[Campus reverse proxy / web server]
    P --> A[Laravel application container]
    A --> M[(Private MySQL)]
    A --> S[Private persistent document storage]
    A --> Q[Queue backend]
    W[Worker + scheduler] --> Q
    W --> M
    W --> S
    B[Restricted backup target] --- M
    B --- S
```

Select exact host OS, PHP runtime/web server, queue backend and Docker production layout during implementation. Existing `artisan serve`, Vite HMR and development credentials are development settings; prepare a separate operational configuration and release image.

## Environment register

| Environment | Current evidence | Planned access |
|---|---|---|
| Developer Docker | Verified on 2026-10-16; local and GitHub foundation checks passed; see foundation validation report | Developer loopback ports 8085/5178 |
| Campus staging | Not configured / no deployment recorded | Restricted MIS/tester LAN segment; synthetic or authorized test data |
| Campus production | Not configured / no deployment recorded | Approved BISU LAN/Wi-Fi segments through internal HTTPS |

## Preparation work

- [ ] Assign server, stable address, internal hostname, firewall rules and network/VLAN access.
- [ ] Confirm how students/alumni reach the allowed campus network; provide kiosk/assisted access if personal devices cannot join it.
- [ ] Provision HTTPS certificates trusted by managed and intended client devices using the MIS-approved method; test trust and renewal.
- [ ] Select durable database/file storage, storage capacity and server power protection/recovery arrangements.
- [ ] Build immutable release images with compiled local assets and fonts; eliminate runtime CDN dependencies, including starter externally linked fonts where applicable.
- [ ] Configure `APP_ENV=production`, debug off, exact internal `APP_URL`, proxy trust, cookies, origins and production secrets without exposing values in docs.
- [ ] Keep MySQL/Redis private; expose only the necessary application endpoint to approved campus networks.
- [ ] Configure persistent private files, worker/scheduler, logs, health checks and disk/job/backup alerts.
- [ ] Configure account verification/password recovery through an approved campus mail relay or reviewed assisted process; the log mailer is not an operational delivery channel.
- [ ] Store application key/secrets securely with controlled recovery access; preserve the key across releases.

## Release sequence

1. Record candidate revision/image digest, included modules, migrations and known issues.
2. Deploy to campus staging with separate database, keys, files and credentials.
3. Run migrations, smoke checks, UAT, LAN-only dependency checks and restore/rollback rehearsal.
4. Obtain named Registrar, Cashier and MIS acceptance required by campus process. Record approval evidence and maintenance window.
5. Back up production data/files; verify recovery points before schema/data changes.
6. Deploy the approved release, apply reviewed migrations, refresh configuration/routes as appropriate, restart workers and verify health.
7. Check authentication, one synthetic approved test path, queue display, private files and report access from actual campus clients.
8. Record deployment timestamp, internal URL, revision, deployer, approver and result in both this document and the index.
9. Observe the agreed initial support period, reconcile office activity and record incidents/rollback decisions.

## Backup, recovery and continuity

- Agree recovery-point and recovery-time targets, retention, backup schedule and responsible operator before real data is entered.
- Back up MySQL, private document files and required configuration/key recovery material with controlled access; keep a copy outside the application host.
- A Docker volume is persistence, not a backup. Verify scheduled backups and restore them periodically to an isolated environment.
- Use compatible/additive migrations where possible. Code rollback and database rollback are separate decisions; irreversible migrations need a tested restore/forward-fix plan.
- Reconcile transactions written after a recovery point before returning to normal work. Preserve evidence and avoid silent financial data loss.
- Keep a manual request/queue/payment-reference log for outages and an authorized reconciliation procedure after recovery.

## Deployment acceptance

- [ ] All Part 13 release criteria met and deployment authority recorded.
- [ ] Campus clients resolve and trust the internal HTTPS endpoint.
- [ ] Core services continue with campus Internet disconnected; server/LAN outage fallback is practiced.
- [ ] Backups, restore, monitoring, worker startup and host restart recovery are tested.
- [ ] No database or debug endpoint is exposed beyond the intended network scope.
- [ ] Release ledger contains real evidence; status remains not deployed until this is completed.

## Release ledger

| Release / revision | Environment | Internal URL | Deployed at | Operator / approver | Validation / rollback evidence | Outcome |
|---|---|---|---|---|---|---|
| — | Campus staging | Pending | — | Pending | None | Not deployed |
| — | Campus production | Pending | — | Pending | None | Not deployed |
