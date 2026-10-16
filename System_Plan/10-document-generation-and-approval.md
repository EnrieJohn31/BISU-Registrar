# Part 10 — Document generation, templates and approval

[Back to index](index.md) | [Previous](09-cashier-and-payment-control.md) | [Next](11-release-tracking-and-notifications.md)

| Tracking field | Value |
|---|---|
| Plan status | Draft |
| Implementation | Not started |
| Validation | Not started |
| Deployment | Not deployed |
| Owner | Registrar template/signatory owner with developer; names pending |
| Dependencies | Parts 03–05, 08–09 |
| Updated | 2026-10-15 |

## Features and screens

- Approved template inventory for certifications, request forms, authentication forms, CAV-related processing documents and other authorized Registrar outputs.
- Versioned template metadata: service, version, effective date, approved fields, signatory and approver.
- Document data review, draft preview, generation, approval/return and controlled printing/download.
- Generated document history with exact template version and input snapshot.

Start with a small office-approved template set, then expand to the full approved inventory. Do not represent generated forms as officially issued documents until the applicable approval and release steps are complete.

## Implementation steps

1. Obtain approved source templates and sanitized sample outputs; record the campus template owner and acceptance criteria.
2. Map every template field to a verified source or an explicitly reviewed staff input. The maintained client list alone does not supply transcripts or other academic facts.
3. Choose and validate a PDF/document renderer against actual templates, Docker fonts and printing requirements. Bundle necessary assets on the campus server.
4. Implement server-controlled template rendering with escaped fields; do not allow staff-uploaded executable templates or arbitrary code.
5. Save a private generated file, checksum, source snapshot and template version. Queue heavier generation jobs with idempotency and visible failed-job recovery.
6. Mark unapproved previews as drafts. Approval references the exact generated version/hash and the authorized approver.
7. Invalidate approval when document content changes. Preserve previous versions for the authorized retention period.
8. Gate print/download by role and request status. Determine whether final authorization is a wet signature, recorded approval or a separate approved signing process.

## Operating rules

- No invented document wording, seal, signatory, signature or reference numbering policy.
- A recorded approval is not automatically a cryptographic signature or legal certification.
- Store files outside public web storage and enforce authorization on each retrieval.
- Expiring links, if used, still require the approved identity/access model; a LAN address alone is insufficient protection.
- Record regeneration/reprint reasons where needed. A newly generated file must not replace an already approved file silently.
- CAV-related processing must follow the actual campus workflow; do not infer that this app can perform external agency approval.

## Acceptance criteria / checks

- [ ] Registrar visually approves generated outputs using short/long names, multiline fields and multiple pages.
- [ ] Required fields cannot be omitted or sourced from unverified client edits.
- [ ] Exact issued version remains recoverable after a template update.
- [ ] Failed/retried jobs cannot create competing approved originals.
- [ ] Unauthorized users cannot access private document paths.
- [ ] Content changes invalidate prior approval and block release until reviewed.
- [ ] Fonts, page breaks, seals and signature spaces print correctly from the campus environment without runtime Internet access.

## Decisions / evidence

Resolve [D08](90-decisions-and-open-questions.md): approved templates, document numbering, signatories, print rules, signing authority and retention. Completion evidence includes approved reference outputs, visual comparisons, access tests and version/approval tests.
