param([switch]$Rebuild)

$ErrorActionPreference = 'Stop'
Push-Location $PSScriptRoot
$nodeWasRunning = $false
$finished = $false

function Invoke-Compose {
    & docker compose -f docker-compose.dev.yml @args
    if ($LASTEXITCODE -ne 0) { throw "Docker Compose failed: $args" }
}

try {
    & docker info --format '{{.ServerVersion}}'
    if ($LASTEXITCODE -ne 0) { throw 'Start Docker Desktop with Linux containers, then rerun setup.ps1.' }
    Invoke-Compose config --quiet

    if (!(Test-Path src/.env)) { Copy-Item src/.env.example src/.env }
    if (!(Select-String -Path src/.env -Pattern '^APP_ENV=(local|testing)$' -Quiet)) {
        throw 'This setup script is for local/testing environments. Review APP_ENV before running it.'
    }
    if (!(Test-Path src/composer.lock) -or !(Test-Path src/package-lock.json)) {
        throw 'Both dependency lockfiles are required. Restore them from Git before setup.'
    }

    $nodeWasRunning = [bool](Invoke-Compose ps --status running --quiet node)
    if ($nodeWasRunning) { Invoke-Compose stop node }
    if ($Rebuild) { Invoke-Compose build --pull } else { Invoke-Compose build }
    Invoke-Compose run --rm --no-deps app composer install --no-interaction --prefer-dist
    Invoke-Compose run --rm --no-deps app composer validate --strict
    if (!(Select-String -Path src/.env -Pattern '^APP_KEY=\S+' -Quiet)) {
        Invoke-Compose run --rm --no-deps app php artisan key:generate
    }
    Invoke-Compose up -d --wait db redis
    Invoke-Compose run --rm app php artisan migrate --force
    Invoke-Compose run --rm --no-deps app npm ci --no-audit --no-fund
    Invoke-Compose run --rm --no-deps app npm run build
    Invoke-Compose up -d
    $finished = $true
    Write-Host 'BISU Registrar is available at http://localhost:8085'
    Write-Host 'Run ./check.ps1 to verify the foundation.'
} finally {
    if ($nodeWasRunning -and !$finished) {
        & docker compose -f docker-compose.dev.yml up -d node
    }
    Pop-Location
}
