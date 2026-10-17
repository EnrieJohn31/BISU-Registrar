#!/bin/sh
set -eu
hook_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
expect_allowed() {
    if ! printf '%s\n' "$1" | sh "$hook_dir/check-attribution.sh" >/dev/null 2>&1; then
        printf '%s\n' 'Expected human attribution to pass.' >&2; exit 1
    fi
}
expect_rejected() {
    if printf '%s\n' "$1" | sh "$hook_dir/check-attribution.sh" >/dev/null 2>&1; then
        printf '%s\n' 'Expected AI attribution to be rejected.' >&2; exit 1
    fi
}
expect_allowed 'feat: update Registrar screens'
expect_allowed 'Co-authored-by: EnrieJohn31 <human@example.test>'
expect_allowed 'Co-authored-by: Claude Martin <human@example.test>'
expect_allowed 'docs: explain how to remove Claude attribution'
expect_rejected 'Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>'
expect_rejected 'Co-authored-by: Claude Sonnet 5.5 <noreply@anthropic.com>'
expect_rejected 'Co-authored-by: Other label <noreply@anthropic.com>'
expect_rejected 'Co-authored-by: Claude Code <tool@example.test>'
expect_rejected 'Co-authored-by: claude[bot] <tool@example.test>'
expect_rejected 'Co-authored-by: Codex <tool@example.test>'
expect_rejected 'Co-authored-by: GitHub Copilot <tool@example.test>'
expect_rejected 'co-AUTHORED-by: chatgpt <tool@example.test>'
expect_rejected 'Co-authored-by: OpenAI <tool@example.test>'
expect_rejected 'Co-authored-by: Anthropic <tool@example.test>'
expect_rejected 'Co-authored-by: Google Gemini <tool@example.test>'
expect_rejected 'Co-authored-by: Cursor <tool@example.test>'
printf '%s\n' 'Attribution guard: 16 cases passed.'
