[CmdletBinding(DefaultParameterSetName = 'Start')]
param(
    [Parameter(Mandatory, ParameterSetName = 'Start')]
    [string]$Address,
    [Parameter(Mandatory, ParameterSetName = 'Stop')]
    [switch]$Stop
)

$ErrorActionPreference = 'Stop'
$previousAddress = $env:BISU_LAN_ADDRESS
Push-Location $PSScriptRoot

function Invoke-DevCompose {
    & docker compose -f docker-compose.dev.yml @args
    if ($LASTEXITCODE -ne 0) { throw "Development Compose failed: $args" }
}

function Invoke-LanCompose {
    & docker compose -f docker-compose.dev.yml -f docker-compose.lan.yml @args
    if ($LASTEXITCODE -ne 0) { throw "LAN Compose failed: $args" }
}

try {
    if (!(Test-Path -LiteralPath 'src/.env') -or
        !(Select-String -LiteralPath 'src/.env' -Pattern '^APP_ENV=(local|testing)$' -Quiet)) {
        throw 'Run ./setup.ps1 first; LAN preview requires a local/testing environment.'
    }

    if ($Stop) {
        Invoke-DevCompose run --rm --no-deps app php artisan config:clear
        Invoke-DevCompose up -d --no-deps app worker scheduler node
        Write-Host 'Loopback development restored: http://localhost:8085; Vite running.'
        Write-Host 'Remove any preview firewall rule in administrator PowerShell: Remove-NetFirewallRule -Name BISU-Registrar-Dev-LAN'
        return
    }

    $parsedAddress = $null
    if (![System.Net.IPAddress]::TryParse($Address, [ref]$parsedAddress) -or
        $parsedAddress.AddressFamily -ne [System.Net.Sockets.AddressFamily]::InterNetwork) {
        throw 'Address must be your PC IPv4 address, not a URL or hostname.'
    }
    $bytes = $parsedAddress.GetAddressBytes()
    $privateAddress = $bytes[0] -eq 10 -or
        ($bytes[0] -eq 172 -and $bytes[1] -ge 16 -and $bytes[1] -le 31) -or
        ($bytes[0] -eq 192 -and $bytes[1] -eq 168)
    $localAddresses = @([System.Net.NetworkInformation.NetworkInterface]::GetAllNetworkInterfaces() |
        Where-Object { $_.OperationalStatus -eq 'Up' } |
        ForEach-Object { $_.GetIPProperties().UnicastAddresses } |
        ForEach-Object { $_.Address.ToString() })
    if (!$privateAddress -or $localAddresses -notcontains $Address) {
        throw 'Use a private IPv4 address assigned to an active network adapter on this PC.'
    }

    $env:BISU_LAN_ADDRESS = $Address
    Invoke-LanCompose config --quiet
    $runningServices = @(Invoke-DevCompose ps --status running --services)
    $nodeWasRunning = $runningServices -contains 'node'
    try {
        # Phones use compiled assets from port 8085; no Vite/HMR port is exposed.
        Invoke-DevCompose stop node
        Invoke-DevCompose run --rm --no-deps app npm run build
        if (Test-Path -LiteralPath 'src/public/hot') {
            Remove-Item -LiteralPath 'src/public/hot'
        }
        Invoke-LanCompose run --rm --no-deps app php artisan config:clear
        Invoke-LanCompose up -d app worker scheduler
    } catch {
        # A failed preview returns the app/background processes to base settings.
        & docker compose -f docker-compose.dev.yml up -d --no-deps app worker scheduler
        if ($nodeWasRunning) { & docker compose -f docker-compose.dev.yml up -d --no-deps node }
        throw
    }
    Write-Host "Phone URL: http://${Address}:8085 (same Wi-Fi; sign in again on the phone)."
    Write-Host 'Vite is paused. After source changes rerun this script; finish with ./lan-preview.ps1 -Stop.'
    Write-Host 'Mailpit remains on the PC at http://localhost:8026. No .env, account or database settings were changed.'
    Write-Host 'If Windows blocks the phone, run the scoped firewall command in docs/LAN-PHONE-PREVIEW.md using administrator PowerShell.'
} finally {
    $env:BISU_LAN_ADDRESS = $previousAddress
    Pop-Location
}
