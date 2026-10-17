$ErrorActionPreference = 'Stop'
Push-Location $PSScriptRoot
try {
    & git rev-parse --git-dir | Out-Null
    if ($LASTEXITCODE -ne 0) { throw 'Run the hook installer from a Git checkout.' }
    $effective = & git config --get core.hooksPath
    if ($effective -and $effective -ne '.githooks') {
        throw "Existing Git hook directory '$effective' was preserved. Integrate the attribution checks there before changing core.hooksPath."
    }
    foreach ($hook in @('check-attribution.sh', 'commit-msg', 'pre-push')) {
        if (!(Test-Path -LiteralPath (Join-Path '.githooks' $hook))) { throw "Missing attribution hook: $hook" }
    }
    & git config --local core.hooksPath .githooks
    if ($LASTEXITCODE -ne 0) { throw 'Could not activate local attribution hooks.' }
    Write-Host 'BISU Registrar commit/push attribution guards are active for this checkout.'
} finally {
    Pop-Location
}
