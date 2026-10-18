param(
    [switch]$SkipBuild,
    [ValidateSet('desktop-light', 'desktop-dark', 'phone-light', 'phone-dark')]
    [string[]]$Project,
    [string]$Grep
)

$ErrorActionPreference = 'Stop'
Push-Location $PSScriptRoot
$testExitCode = 1

function Invoke-BrowserCompose {
    # Both file and project are fixed; never clean up the development project.
    & docker compose -p bisu-registrar-browser -f docker-compose.browser.yml @args
    if ($LASTEXITCODE -ne 0) { throw "Browser Compose failed: $args" }
}

try {
    Invoke-BrowserCompose config --quiet
    Invoke-BrowserCompose down --volumes --remove-orphans
    if (!$SkipBuild) { Invoke-BrowserCompose build prepare }
    Invoke-BrowserCompose run --rm --no-deps prepare
    Invoke-BrowserCompose up -d --wait web
    $browserArguments = @('npm', 'run', 'test:browser', '--')
    foreach ($projectName in $Project) { $browserArguments += @('--project', $projectName) }
    if ($Grep) { $browserArguments += @('--grep', $Grep) }
    & docker compose -p bisu-registrar-browser -f docker-compose.browser.yml run --rm --no-deps browser @browserArguments
    $testExitCode = $LASTEXITCODE
} finally {
    if ($testExitCode -ne 0) {
        & docker compose -p bisu-registrar-browser -f docker-compose.browser.yml logs --tail 80 web
    }
    & docker compose -p bisu-registrar-browser -f docker-compose.browser.yml down --volumes --remove-orphans
    $cleanupExitCode = $LASTEXITCODE
    Pop-Location
    if ($cleanupExitCode -ne 0) { throw 'Browser test cleanup failed; inspect only the bisu-registrar-browser project.' }
}
exit $testExitCode
