param(
    [string]$Source = "",
    [string]$Target = "",
    [switch]$Medium,
    [switch]$Aggressive,
    [switch]$Views
)

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$HtdocsRoot = Split-Path -Parent $ProjectRoot

if ([string]::IsNullOrWhiteSpace($Source)) {
    $Source = $ProjectRoot
}

if ([string]::IsNullOrWhiteSpace($Target)) {
    $Target = Join-Path $HtdocsRoot "foodshop-obfuscated"
}

if (-not (Test-Path -Path $Source)) {
    Write-Error "Source path not found: $Source"
    exit 1
}

$targetsFile = Join-Path $PSScriptRoot "obfuscate-lite-targets.txt"
$targets = @()
if (Test-Path -Path $targetsFile) {
    $targets = Get-Content -Path $targetsFile | ForEach-Object { $_.Trim() } | Where-Object {
        $_ -ne "" -and -not $_.StartsWith("#")
    } | ForEach-Object {
        if ($_ -match "^[A-Za-z]:\\\\") {
            $_
        } else {
            Join-Path $HtdocsRoot $_
        }
    }
}

if ($targets.Count -eq 0) {
    $targets = @($Target)
}

foreach ($targetPath in $targets) {
    if (-not (Test-Path -Path $targetPath)) {
        Write-Host "Target path not found. Creating:" $targetPath
        New-Item -ItemType Directory -Force -Path $targetPath | Out-Null
    }

    Write-Host "Lite obfuscation from:" $Source
    Write-Host "Output target:" $targetPath
    Write-Host ""

    $args = @()
    if ($Aggressive) {
        $args += '--aggressive'
    } elseif ($Medium) {
        $args += '--medium'
    }
    if ($Views) {
        $args += '--views'
    }

    & php (Join-Path $PSScriptRoot "obfuscate-lite.php") $Source $targetPath @args
    if ($LASTEXITCODE -ne 0) {
        Write-Error "obfuscate-lite failed with exit code $LASTEXITCODE"
        exit $LASTEXITCODE
    }
}

Write-Host "Lite obfuscation complete."
