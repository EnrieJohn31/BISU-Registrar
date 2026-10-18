[CmdletBinding(DefaultParameterSetName = 'Start')]
param(
    [Parameter(ParameterSetName = 'Start')]
    [ValidateNotNullOrEmpty()]
    [string]$Address,
    [Parameter(Mandatory, ParameterSetName = 'Stop')]
    [switch]$Stop,
    [Parameter(Mandatory, ParameterSetName = 'Links')]
    [switch]$Links
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

function Test-PrivateIPv4Address([System.Net.IPAddress]$IpAddress) {
    if ($IpAddress.AddressFamily -ne [System.Net.Sockets.AddressFamily]::InterNetwork) { return $false }
    $bytes = $IpAddress.GetAddressBytes()
    return $bytes[0] -eq 10 -or
        ($bytes[0] -eq 172 -and $bytes[1] -ge 16 -and $bytes[1] -le 31) -or
        ($bytes[0] -eq 192 -and $bytes[1] -eq 168)
}

function Get-ActiveLanAddresses {
    foreach ($adapter in [System.Net.NetworkInformation.NetworkInterface]::GetAllNetworkInterfaces()) {
        if ($adapter.OperationalStatus -ne 'Up') { continue }
        $properties = $adapter.GetIPProperties()
        $gateways = @($properties.GatewayAddresses | Where-Object {
            $_.Address.AddressFamily -eq [System.Net.Sockets.AddressFamily]::InterNetwork -and
            $_.Address.ToString() -ne '0.0.0.0'
        })
        foreach ($unicast in $properties.UnicastAddresses) {
            if (Test-PrivateIPv4Address $unicast.Address) {
                [pscustomobject]@{
                    Address = $unicast.Address.ToString()
                    Adapter = $adapter.Name
                    HasGateway = $gateways.Count -gt 0
                }
            }
        }
    }
}

function Show-AccessLinks {
    $appId = Invoke-DevCompose ps -q app
    if (!$appId) {
        Write-Host 'The app is not running. Start it with ./lan-preview.ps1 or double-click start-lan.cmd.'
        return
    }
    $container = (& docker inspect $appId | ConvertFrom-Json)[0]
    if ($LASTEXITCODE -ne 0) { throw 'Could not read running application bindings.' }
    $activeAddresses = @(Get-ActiveLanAddresses)
    $bindings = @($container.HostConfig.PortBindings.'8000/tcp')
    Write-Host ''
    Write-Host 'BISU Registrar - connection links'
    if ($bindings | Where-Object { $_.HostIp -eq '127.0.0.1' -and $_.HostPort -eq '8085' }) {
        Write-Host 'App on this PC:     http://localhost:8085'
    }
    $phoneLinks = 0
    foreach ($binding in $bindings) {
        $adapterAddress = $activeAddresses | Where-Object { $_.Address -eq $binding.HostIp } | Select-Object -First 1
        if ($adapterAddress -and $binding.HostPort -eq '8085') {
            Write-Host ("Phone / same LAN:   http://{0}:8085" -f $adapterAddress.Address)
            Write-Host ("PC IPv4 / adapter:  {0} / {1}" -f $adapterAddress.Address, $adapterAddress.Adapter)
            $phoneLinks++
        }
    }
    if (!$phoneLinks) {
        Write-Host 'Phone access is off or its bound IP is no longer active. Run ./lan-preview.ps1 to start/refresh it.'
    }
    Write-Host 'Test inbox on PC:   http://localhost:8026'
    Write-Host 'Show links again:  ./lan-preview.ps1 -Links'
    Write-Host 'Stop LAN preview:  ./lan-preview.ps1 -Stop'
}

try {
    if ($Links) {
        Show-AccessLinks
        return
    }
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

    $localAddresses = @(Get-ActiveLanAddresses)
    if (!$Address) {
        $candidates = @($localAddresses | Where-Object { $_.HasGateway } | Sort-Object Address -Unique)
        if ($candidates.Count -ne 1) {
            $choices = @($localAddresses | ForEach-Object { '{0} ({1})' -f $_.Address, $_.Adapter }) -join ', '
            throw "Could not choose one LAN IPv4 address automatically. Active private addresses: $choices. Run ./lan-preview.ps1 -Address <your-PC-LAN-IPv4>."
        }
        $Address = $candidates[0].Address
    }
    $parsedAddress = $null
    if (![System.Net.IPAddress]::TryParse($Address, [ref]$parsedAddress) -or
        $parsedAddress.AddressFamily -ne [System.Net.Sockets.AddressFamily]::InterNetwork) {
        throw 'Address must be your PC IPv4 address, not a URL or hostname.'
    }
    $assignedAddresses = @($localAddresses | ForEach-Object { $_.Address })
    if (!(Test-PrivateIPv4Address $parsedAddress) -or $assignedAddresses -notcontains $Address) {
        throw 'Use a private IPv4 address assigned to an active network adapter on this PC.'
    }
    $selectedAdapter = $localAddresses | Where-Object { $_.Address -eq $Address } | Select-Object -First 1
    Write-Host ("Selected PC IPv4: {0} ({1})" -f $Address, $selectedAdapter.Adapter)

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
    Show-AccessLinks
    Write-Host 'Vite is paused. After source changes rerun this script; finish with ./lan-preview.ps1 -Stop.'
    Write-Host 'Mailpit remains on the PC at http://localhost:8026. No .env, account or database settings were changed.'
    Write-Host 'If your IP changes, refresh any address-specific firewall rule. See docs/LAN-PHONE-PREVIEW.md.'
} finally {
    $env:BISU_LAN_ADDRESS = $previousAddress
    Pop-Location
}
