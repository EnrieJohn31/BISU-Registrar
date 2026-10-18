$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$repositoryPath = Split-Path $PSScriptRoot -Parent
$sourcePath = Join-Path $repositoryPath 'src/resources/branding/bisu-seal-source.png'
$publicPath = Join-Path $repositoryPath 'src/public'
$brandingPath = Join-Path $publicPath 'branding'
New-Item -ItemType Directory -Path $brandingPath -Force | Out-Null
$sourceImage = [System.Drawing.Image]::FromFile($sourcePath)

function Convert-SealToPngBytes([int]$Size) {
    $bitmap = New-Object System.Drawing.Bitmap($Size, $Size, ([System.Drawing.Imaging.PixelFormat]::Format32bppArgb))
    $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
    $attributes = New-Object System.Drawing.Imaging.ImageAttributes
    $stream = New-Object System.IO.MemoryStream
    try {
        $graphics.Clear([System.Drawing.Color]::Transparent)
        $graphics.CompositingMode = [System.Drawing.Drawing2D.CompositingMode]::SourceCopy
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
        $attributes.SetWrapMode([System.Drawing.Drawing2D.WrapMode]::TileFlipXY)
        $scale = [Math]::Min($Size / $sourceImage.Width, $Size / $sourceImage.Height)
        $width = [int][Math]::Round($sourceImage.Width * $scale)
        $height = [int][Math]::Round($sourceImage.Height * $scale)
        $rectangle = New-Object System.Drawing.Rectangle(([int](($Size - $width) / 2)), ([int](($Size - $height) / 2)), $width, $height)
        $graphics.DrawImage($sourceImage, $rectangle, 0, 0, $sourceImage.Width, $sourceImage.Height, [System.Drawing.GraphicsUnit]::Pixel, $attributes)
        $bitmap.Save($stream, [System.Drawing.Imaging.ImageFormat]::Png)
        return ,$stream.ToArray()
    } finally {
        $stream.Dispose()
        $attributes.Dispose()
        $graphics.Dispose()
        $bitmap.Dispose()
    }
}

try {
    [System.IO.File]::WriteAllBytes((Join-Path $brandingPath 'bisu-seal.png'), (Convert-SealToPngBytes 96))
    [System.IO.File]::WriteAllBytes((Join-Path $publicPath 'favicon.png'), (Convert-SealToPngBytes 64))
    [System.IO.File]::WriteAllBytes((Join-Path $publicPath 'apple-touch-icon.png'), (Convert-SealToPngBytes 180))

    # ICO directory entries point to lossless PNG frames at native favicon sizes.
    $frames = @(16, 32, 48 | ForEach-Object {
        [pscustomobject]@{ Size = $_; Bytes = Convert-SealToPngBytes $_ }
    })
    $iconStream = New-Object System.IO.MemoryStream
    $writer = New-Object System.IO.BinaryWriter($iconStream)
    try {
        $writer.Write([uint16]0)
        $writer.Write([uint16]1)
        $writer.Write([uint16]$frames.Count)
        $offset = 6 + 16 * $frames.Count
        foreach ($frame in $frames) {
            $writer.Write([byte]$frame.Size)
            $writer.Write([byte]$frame.Size)
            $writer.Write([byte]0)
            $writer.Write([byte]0)
            $writer.Write([uint16]1)
            $writer.Write([uint16]32)
            $writer.Write([uint32]$frame.Bytes.Length)
            $writer.Write([uint32]$offset)
            $offset += $frame.Bytes.Length
        }
        foreach ($frame in $frames) { $writer.Write([byte[]]$frame.Bytes) }
        $writer.Flush()
        [System.IO.File]::WriteAllBytes((Join-Path $publicPath 'favicon.ico'), $iconStream.ToArray())
    } finally {
        $writer.Dispose()
        $iconStream.Dispose()
    }
} finally {
    $sourceImage.Dispose()
}
Write-Host 'Generated local BISU seal (96px), favicon (64px, ICO 16/32/48px) and touch icon (180px).'
