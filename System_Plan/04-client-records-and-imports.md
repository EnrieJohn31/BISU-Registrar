# Part 04 — Verified student/alumni records and imports

[Back to index](index.md) | [Previous](03-accounts-permissions-and-audit.md) | [Next](05-services-requirements-and-fees.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft; initial record-source choice confirmed |
| Implementation | Not started |
| Validation | Not started |
| Deployment | Not deployed |
| Owner | Authorized Registrar records custodian with developer; assignment pending |
| Dependencies | Parts 02–03 |
| Updated | 2026-10-15 |

## Confirmed direction

Authorized staff will maintain or import a verified client list initially. A direct integration with an existing official database is a later option. These profiles identify requesters; the initial import does not establish a replacement academic records system.

Client self-service is available on the BISU campus network. Campus LAN access does not establish client identity; account and identity checks still apply.

## Features and screens

- Client search by authorized Student ID or Alumni ID, with masked results where appropriate.
- Client detail, verification state, identifier history, source and last verification date.
- Staff create/update and duplicate-review workflow with reasons and audit trail.
- Import upload, column mapping/template, preview, validation errors and batch results.
- Account-to-client linking and mismatch/manual-review handling.
- Support a person who has both student and alumni identifiers without duplicating their history.

## Proposed data and rules

Minimum fields: internal client key, identifier type/value, name, client category, approved program/course reference if needed, verification state, provenance and authorized verifier. Contact details are optional until an approved process requires them. Never import more fields merely because the source file contains them.

- A Student ID or Alumni ID identifies a record; knowledge of it alone is not authentication.
- Use approved identity proof or a verified channel before linking an online account. Show neutral lookup messages to avoid exposing who is in the database.
- Keep email verification separate from official client identity verification.
- Identifier changes preserve aliases/history and require staff authorization.
- Client-supplied corrections do not automatically overwrite official verified fields.

## Implementation steps

1. Agree on the initial CSV format, field dictionary, source custodian and duplicate rules.
2. Add client and identifier migrations, policies, factories and audit events.
3. Build staff CRUD/search and the review queue for mismatched identity claims.
4. Implement staged import: authorize upload → validate limits/type/encoding → dry-run → review → explicit commit.
5. Validate identifiers, required fields, duplicate rows and existing-record conflicts before commit. Report row-level failures without exposing unnecessary information.
6. Store file/batch checksum and import provenance. Prevent accidental reimport of the same batch; require an explicit reviewed update mode for changed records.
7. Process large imports in bounded batches with per-row outcomes and safe retries. Do not leave half-linked identities when a row fails.
8. Build account linking and audit it; release staged raw files according to the approved retention policy.

## Acceptance criteria / checks

- [ ] Valid imports produce the expected unique clients and identifiers.
- [ ] Duplicate IDs, missing IDs, invalid encodings and conflicting identities are clearly reported.
- [ ] Replaying a committed batch cannot duplicate clients or overwrite verified records silently.
- [ ] Existing requests retain their client association after a legitimate profile correction.
- [ ] An unverified person with a known ID cannot retrieve private details or link that account.
- [ ] Staff-assisted intake records both the operator and the represented client.

## Open decisions and evidence

Confirmed: initial maintained/imported verified list. Pending [D03–D04](90-decisions-and-open-questions.md): identity proof, approved columns, source ownership and correction procedure. Completion requires a synthetic import demonstration and records-custodian acceptance; do not put actual student files in this plan folder.
