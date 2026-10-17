# Contributor attribution cleanup - 2026-10-17

[Current handoff](../System_Plan/handoff.md)

## Finding

Five commit messages carried `Co-Authored-By: Claude ... <noreply@anthropic.com>` trailers. Human author/committer fields already belong to the project owner. The trailers grant co-author credit; they do not grant repository access. GitHub's contributors API currently returns only the owner's account, while the published commit messages still contain three Claude credits.

## Local work completed

| Original local commit | Replacement | Files |
|---|---|---|
| `878964d` (local-only policy) | `8402d30` | Identical tree |
| `cca3936` (Part 04 slice 1) | `d577d76` | Identical tree |

Both local branch pointers were updated atomically after a full Git bundle backup. Author, committer and timestamps were preserved. Application/index content and the unrelated `src/nul` were untouched. AGENTS.md now records the owner's preference against AI co-author/generated-by credits; this is an instruction, not a global tool-configuration change.

## Published cleanup prepared, not applied

Original published trailers are in `be55386`, `52b58fa` and `4e897df`. Removing them changes descendant commit IDs. An isolated bare mirror has been prepared and checked: 20 rewritten commit objects, three removed trailers, every old/new file tree identical, no Claude trailers in its reachable commit messages, and Git fsck passed. Four inherited signatures were removed because modifying signed commit objects invalidates them; no signature was forged.

### Proposed published branch replacements

| Branch | Current tip | Prepared tip |
|---|---|---|
| `chore/docker-dev-performance` | `6cbaf74` | `7ea31e4` |
| `docs/part-03-accounts-design-2026-10-17` | `9f11525` | `1b6bbf2` |
| `docs/part-04-client-import-design` | `c095ee0` | `09a56c9` |
| `feat/part-03-accounts-roles-audit` | `4abd011` | `7d6b0cb` |
| `main` | `ce9cc61` | `2e83568` |

Main would retain exactly its current application/documentation tree. Only its commit metadata/ancestry changes. The local Part 04 implementation and these new cleanup notes must remain local under the owner's Git policy. Old PR/commit links can retain the original objects; this is not a server-side purge of historical PR references.

## Approval boundary / execution plan

1. Owner reviews this metadata-only replacement and approves temporarily adjusting main protection to allow the repository administrator's guarded rewrite. Current main: PR required, Foundation checks required, administrators enforced, force-pushes blocked.
2. Re-read remote refs and protection; stop if branch tips changed. Keep a recovery bundle and the original protection settings. Update only the five listed existing remote branches with explicit expected-tip leases, atomically where supported. Never mirror-push all local branches or publish Part 04 implementation.
3. Restore and verify every protection setting immediately, including if the rewrite fails. Update local branches by the reviewed mapping without changing their file trees or discarding untracked work. Verify the public tips and run/check CI on the replacement main revision.
4. Recheck GitHub contributor displays after its cache refresh. Existing collaborators need to rebase/resynchronize their clones; old SHA links/signature displays change. GitHub says statistics can take about 24 hours to refresh; contact GitHub Support if still stale afterward.

## Recovery / validation

Bundle, local/published commit maps and isolated mirror are under ignored `.local-backups/contributor-cleanup-2026-10-17T12-30-04-860Z`. The mirror's default push URL is deliberately disabled. No remote changes or branch-protection updates have been made.

Validation performed: each rewritten commit tree compared to the original; current index/working-tree content unchanged after the local rewrite; no matching trailers in prepared reachable history; Git object/ref integrity checked. Application tests are not needed for a metadata/documentation-only operation; the existing Part 04 implementation validation remains applicable.

Sources: [GitHub commit-message rewriting](https://docs.github.com/en/pull-requests/how-tos/commit-changes/changing-a-commit-message), [protected branches](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches), [contributor refresh behavior](https://docs.github.com/en/repositories/viewing-activity-and-data-for-your-repository/viewing-a-projects-contributors).
