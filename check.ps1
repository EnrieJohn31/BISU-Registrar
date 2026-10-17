$ErrorActionPreference = 'Stop'
Push-Location $PSScriptRoot
$nodeWasRunning = $false

function Invoke-Compose {
    & docker compose -f docker-compose.dev.yml @args
    if ($LASTEXITCODE -ne 0) { throw "Docker Compose failed: $args" }
}

try {
    & docker info --format '{{.ServerVersion}}'
    if ($LASTEXITCODE -ne 0) { throw 'Start Docker Desktop with Linux containers before running check.ps1.' }
    if (!(Test-Path src/.env)) {
        throw 'The local environment file is missing. Run ./setup.ps1 first.'
    }
    if (!(Select-String -Path src/.env -Pattern '^APP_ENV=(local|testing)$' -Quiet)) {
        throw 'Run development checks only against a local/testing environment.'
    }
    Invoke-Compose config --quiet
    # Dependencies live in Docker volumes, not in src/, so check inside the container.
    & docker compose -f docker-compose.dev.yml run --rm --no-deps app sh -c 'test -f vendor/autoload.php && test -d node_modules/.bin'
    if ($LASTEXITCODE -ne 0) { throw 'Dependencies are missing from the Docker volumes. Run ./setup.ps1 first.' }
    $nodeWasRunning = [bool](Invoke-Compose ps --status running --quiet node)
    if ($nodeWasRunning) { Invoke-Compose stop node }

    Invoke-Compose run --rm --no-deps app composer validate --strict
    Invoke-Compose run --rm --no-deps app npm run build
    Invoke-Compose run --rm --no-deps app npm run check
    Invoke-Compose run --rm --no-deps app npm run types:check
    Invoke-Compose run --rm --no-deps app composer test
    Write-Host 'Foundation checks passed.'
} finally {
    if ($nodeWasRunning) { & docker compose -f docker-compose.dev.yml up -d node }
    Pop-Location
}
