# Contributor attribution cleanup — 2026-10-17

[Current handoff](../System_Plan/handoff.md)

## Result

The project owner approved the GitHub history rewrite and temporary main-protection adjustment, then clarified that all project authors/co-authors must be actual people, never AI providers/tools/models. Claude credits were removed from two local commits and three published commits. Application file trees, human author identities and timestamps were preserved.

An atomic push with exact expected-tip leases replaced five existing public branch tips. Every original main protection setting was restored and verified immediately afterward: administrator enforcement, required PR/CI, strict checks, conversation resolution, no force pushes/deletion and the original other flags. Four old signatures were removed because changing commit objects invalidates them. No signature was forged.

## Published history replacements

| Branch | Before cleanup | Cleaned tip |
|---|---|---|
| `main` | `ce9cc61` | `2e83568` |
| `chore/docker-dev-performance` | `6cbaf74` | `7ea31e4` |
| `docs/part-03-accounts-design-2026-10-17` | `9f11525` | `1b6bbf2` |
| `docs/part-04-client-import-design` | `c095ee0` | `09a56c9` |
| `feat/part-03-accounts-roles-audit` | `4abd011` | `7d6b0cb` |

The rewrite changed 20 commit objects with identical old/new file trees. Original trailers were in `be55386`, `52b58fa` and `4e897df`. The cleanup did not publish the Part 04 client-record feature. Later prevention tooling is delivered separately through [PR #10](https://github.com/EnrieJohn31/BISU-Registrar/pull/10); it merged as `c331ae3` after [required CI passed](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/39033657347).

## Local branch alignment

| Local work | Original | First local cleanup | After cleaned-base alignment |
|---|---|---|---|
| Local-only policy | `878964d` | `8402d30` | `069e6cb` |
| Part 04 slice 1 | `cca3936` | `d577d76` | `0ae71f6` |
| Attribution cleanup record | `614eb42` | — | `c9315d7` |

All local branches were aligned atomically to the cleaned ancestry. Current file/index content and the unrelated untracked `src/nul` were preserved. Part 04 implementation stays on `feat/part-04-client-records`, unpushed; its existing tests/browser validation still apply because the implementation did not change.

## Recurrence prevention

- AGENTS.md explicitly requires human authors/co-authors and prohibits AI provider/model/tool credits and generated-by signatures.
- Shared `commit-msg` and `pre-push` hooks reject recognized AI attribution, including Claude/Anthropic, OpenAI/Codex/ChatGPT and other supported tool/provider labels. Ordinary mentions in descriptions and actual human co-authors remain allowed.
- `install-git-hooks.ps1` was run and the guards are active in this checkout; setup runs the installer for new checkouts. Existing custom hook paths are preserved for manual integration. Hooks are local to each clone; required CI provides a second check.
- Required Foundation CI fetches full history, checks primary authors/co-author trailers, and runs 16 behavioral cases. Eleven integration/configuration checks covered actual Git hook invocation, historic-credit reintroduction, AI primary authors, custom-hook preservation and YAML/setup syntax.
- Repository owners can deliberately alter or bypass controls; automated work must never do so. Existing clones must install the hooks and synchronize the rewritten history. We do not claim an external clone's settings changed automatically.

## Claude Code default attribution

Installed Claude Code is 2.1.120. The [official settings reference](https://code.claude.com/docs/en/settings-reference#attribution) confirms it adds commit/PR attribution by default and accepts empty `attribution.commit` and `attribution.pr` strings to suppress both. Added those project-scoped values in `.claude/settings.json`, validated the JSON, and kept the independent Git/CI guards active. No global configuration, historical disclosure records or session metadata was changed. A fresh Claude Code session in this project ensures it reads the configuration. This follow-up remains local until the owner asks to publish.

## Verification / recovery

- Original and rewritten commit trees compared equal; no reachable Claude trailers remained across aligned local refs.
- GitHub API verified 24 published commits, zero Claude trailers and only `Enrie John Edem` / `EnrieJohn31` author names. [Clean-main Foundation CI passed](https://github.com/EnrieJohn31/BISU-Registrar/actions/runs/39033220866).
- All-ref recovery bundles, original/restored protection JSON, commit maps and publication/alignment reports are retained under ignored `.local-backups/contributor-cleanup-2026-10-17T12-30-04-860Z/`. The preparation mirror's default push URL remains disabled.
- GitHub contributor displays may take about 24 hours to refresh. Old PR/commit links can retain original objects; this operation did not request a server-side purge. Re-clone/rebase existing collaborators' work onto cleaned history before publishing, so old credits cannot be reintroduced.

Sources: [GitHub history rewriting](https://docs.github.com/en/pull-requests/how-tos/commit-changes/changing-a-commit-message), [protected branches](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches), [contributor display refresh](https://docs.github.com/en/repositories/viewing-activity-and-data-for-your-repository/viewing-a-projects-contributors).
