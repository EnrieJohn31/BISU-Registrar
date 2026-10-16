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