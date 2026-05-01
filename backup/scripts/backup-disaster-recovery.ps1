[CmdletBinding()]
param(
    [string]$ProjectRoot
)

function Get-ProjectRoot {
    param([string]$ScriptRoot)

    return (Resolve-Path (Join-Path $ScriptRoot '..\..')).Path
}

function Get-EnvValue {
    param(
        [string]$EnvPath,
        [string]$Name
    )

    $match = Select-String -Path $EnvPath -Pattern ('^\s*' + [regex]::Escape($Name) + '\s*=') | Select-Object -First 1
    if (-not $match) {
        return $null
    }

    $value = ($match.Line -split '=', 2)[1].Trim()
    if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
        $value = $value.Substring(1, $value.Length - 2)
    }

    return $value
}

function Resolve-Tool {
    param(
        [string[]]$Candidates,
        [string]$CommandName
    )

    foreach ($candidate in $Candidates) {
        if (Test-Path $candidate) {
            return $candidate
        }
    }

    $command = Get-Command $CommandName -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    throw "Unable to find $CommandName. Install MySQL client tools or update the script paths."
}

if (-not $ProjectRoot) {
    $ProjectRoot = Get-ProjectRoot -ScriptRoot $PSScriptRoot
}

$EnvPath = Join-Path $ProjectRoot '.env'
if (-not (Test-Path $EnvPath)) {
    throw "Cannot find .env at $EnvPath"
}

$DbHost = Get-EnvValue -EnvPath $EnvPath -Name 'database.default.hostname'
$DbName = Get-EnvValue -EnvPath $EnvPath -Name 'database.default.database'
$DbUser = Get-EnvValue -EnvPath $EnvPath -Name 'database.default.username'
$DbPassword = Get-EnvValue -EnvPath $EnvPath -Name 'database.default.password'
$DbPort = Get-EnvValue -EnvPath $EnvPath -Name 'database.default.port'

if ([string]::IsNullOrWhiteSpace($DbHost) -or [string]::IsNullOrWhiteSpace($DbName) -or [string]::IsNullOrWhiteSpace($DbUser)) {
    throw 'Database connection values are missing in .env.'
}

if ([string]::IsNullOrWhiteSpace($DbPort)) {
    $DbPort = '3306'
}

$BackupRoot = Join-Path $ProjectRoot 'writable\backups'
$Timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$StagingRoot = Join-Path $BackupRoot ("staging-$Timestamp")
$ArchiveFile = Join-Path $BackupRoot ("oabsc-dr-$Timestamp.zip")
$DatabaseFile = Join-Path $BackupRoot ("oabsc-db-$Timestamp.sql")
$LatestZip = Join-Path $BackupRoot 'latest.zip'
$LatestSql = Join-Path $BackupRoot 'latest.sql'

New-Item -ItemType Directory -Force -Path $BackupRoot | Out-Null
New-Item -ItemType Directory -Force -Path $StagingRoot | Out-Null

$MysqlDump = Resolve-Tool -Candidates @(
    'C:\xampp\mysql\bin\mysqldump.exe',
    'C:\xampp\mysql\bin\mysqldump'
) -CommandName 'mysqldump'

$DumpArgs = @(
    '--host=' + $DbHost
    '--port=' + $DbPort
    '--user=' + $DbUser
    '--single-transaction'
    '--routines'
    '--triggers'
    '--events'
    '--default-character-set=utf8mb4'
    '--result-file=' + $DatabaseFile
    $DbName
)

if (-not [string]::IsNullOrWhiteSpace($DbPassword)) {
    $DumpArgs = @('--password=' + $DbPassword) + $DumpArgs
}

Write-Host "Backing up database to $DatabaseFile"
& $MysqlDump @DumpArgs
if ($LASTEXITCODE -ne 0) {
    throw 'mysqldump failed.'
}

Copy-Item -Path $DatabaseFile -Destination $LatestSql -Force

$Manifest = [ordered]@{
    generatedAt = (Get-Date).ToString('s')
    project     = 'OABSC'
    database    = @{
        host = $DbHost
        name = $DbName
        port = $DbPort
    }
    files = @(
        'uploads\profiles',
        'writable\uploads'
    )
}

$ManifestPath = Join-Path $StagingRoot 'manifest.json'
$StagingDbDir = Join-Path $StagingRoot 'database'
$StagingFilesDir = Join-Path $StagingRoot 'files'
New-Item -ItemType Directory -Force -Path $StagingDbDir | Out-Null
New-Item -ItemType Directory -Force -Path $StagingFilesDir | Out-Null

Copy-Item -Path $DatabaseFile -Destination (Join-Path $StagingDbDir (Split-Path $DatabaseFile -Leaf)) -Force

$Assets = @(
    @{ Source = Join-Path $ProjectRoot 'uploads'; Destination = Join-Path $StagingFilesDir 'uploads' },
    @{ Source = Join-Path $ProjectRoot 'writable\uploads'; Destination = Join-Path $StagingFilesDir 'writable\uploads' }
)

foreach ($asset in $Assets) {
    if (Test-Path $asset.Source) {
        New-Item -ItemType Directory -Force -Path (Split-Path $asset.Destination -Parent) | Out-Null
        Copy-Item -Path $asset.Source -Destination $asset.Destination -Recurse -Force
    }
}

$Manifest | ConvertTo-Json -Depth 6 | Set-Content -Path $ManifestPath -Encoding UTF8

if (Test-Path $ArchiveFile) {
    Remove-Item $ArchiveFile -Force
}

Compress-Archive -Path (Join-Path $StagingRoot '*') -DestinationPath $ArchiveFile -Force
Copy-Item -Path $ArchiveFile -Destination $LatestZip -Force

Remove-Item -Path $StagingRoot -Recurse -Force

Write-Host ''
Write-Host 'Backup completed successfully.'
Write-Host "SQL backup: $LatestSql"
Write-Host "Archive backup: $LatestZip"
