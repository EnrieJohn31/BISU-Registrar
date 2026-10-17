#!/bin/sh
# Read commit attribution from stdin. Human authors/co-authors remain allowed.
pattern='^[[:space:]]*Co-authored-by:[[:space:]]*((Claude([[:space:]]+(Code|Sonnet|Opus|Haiku)[^<]*)?|claude\[bot\]|ChatGPT|OpenAI([[:space:]]+Codex)?|Codex|GitHub Copilot|Copilot|(Google[[:space:]]+)?Gemini|Anthropic|DeepSeek|Cursor)[[:space:]]*<|.*<(noreply@anthropic\.com|noreply@openai\.com)>)'
LC_ALL=C grep -Eiq "$pattern"
result=$?
case "$result" in
    0)
        printf '%s\n' 'AI author/co-author credit is disabled for BISU Registrar. Remove the AI attribution trailer and retain the human author.' >&2
        exit 1
        ;;
    1) exit 0 ;;
    *) printf '%s\n' 'Unable to validate commit attribution; refusing to continue.' >&2; exit 1 ;;
esac
