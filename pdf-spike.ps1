param(
    [switch]$SkipBuild,
    [ValidateRange(1, 5)]
    [int]$Repeats = 3
)

$ErrorActionPreference = 'Stop'
Push-Location $PSScriptRoot
function Invoke-PdfCompose {
    & docker compose -p bisu-registrar-pdf-spike -f docker-compose.pdf-spike.yml @args
    if ($LASTEXITCODE -ne 0) { throw "PDF spike failed: $args" }
}
try {
    New-Item -ItemType Directory -Path output/pdf, tmp/pdfs -Force | Out-Null
    Invoke-PdfCompose config --quiet
    # Delete only this runner's packs/manifest; never present a stale success after failure.
    foreach ($name in @('dompdf-sample-pack.pdf', 'chromium-sample-pack.pdf', 'results.json')) {
        $outputPath = Join-Path $PSScriptRoot ('output/pdf/' + $name)
        if (Test-Path -LiteralPath $outputPath) { Remove-Item -LiteralPath $outputPath }
    }
    if (!$SkipBuild) { Invoke-PdfCompose build qa dompdf chromium }
    Invoke-PdfCompose run --rm --no-deps qa python build-fixtures.py
    # Sequential runs avoid competing with each other for the measured CPU allowance.
    Invoke-PdfCompose run --rm --no-deps -e "PDF_SPIKE_REPEATS=$Repeats" dompdf
    Invoke-PdfCompose run --rm --no-deps -e "PDF_SPIKE_REPEATS=$Repeats" chromium
    Invoke-PdfCompose run --rm --no-deps qa
    Write-Host 'PDF spike complete. Read output/pdf/results.json and inspect output/pdf/previews before accepting layout.'
} finally {
    & docker compose -p bisu-registrar-pdf-spike -f docker-compose.pdf-spike.yml down --remove-orphans
    $cleanupExitCode = $LASTEXITCODE
    Pop-Location
    if ($cleanupExitCode -ne 0) { throw 'PDF spike cleanup failed; inspect only the bisu-registrar-pdf-spike project.' }
}
