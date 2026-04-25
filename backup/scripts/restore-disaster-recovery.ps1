[CmdletBinding()]
param(
    [string]$ProjectRoot,
    [string]$BackupPath,
    [switch]$DropExisting
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
if (-not $BackupPath) {
    $Candidates = @(
        (Join-Path $BackupRoot 'latest.zip'),
        (Join-Path $BackupRoot 'latest.sql'),
        (Join-Path $ProjectRoot 'backup\backup.sql')
    )

    foreach ($candidate in $Candidates) {
        if (Test-Path $candidate) {
            $BackupPath = $candidate
            break
        }
    }
}

if (-not $BackupPath -or -not (Test-Path $BackupPath)) {
    throw 'No backup file found. Run the backup script first or pass -BackupPath.'
}

$Mysql = Resolve-Tool -Candidates @(
    'C:\xampp\mysql\bin\mysql.exe',
    'C:\xampp\mysql\bin\mysql'
) -CommandName 'mysql'

$CommonArgs = @(
    '--host=' + $DbHost
    '--port=' + $DbPort
    '--user=' + $DbUser
    '--default-character-set=utf8mb4'
)

if (-not [string]::IsNullOrWhiteSpace($DbPassword)) {
    $CommonArgs = @('--password=' + $DbPassword) + $CommonArgs
}

$ExtractRoot = $null
try {
    $SqlPath = $null
    $IsZip = $BackupPath.ToLower().EndsWith('.zip')

    if ($IsZip) {
        $ExtractRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("oabsc-restore-" + [guid]::NewGuid().ToString('N'))
        New-Item -ItemType Directory -Force -Path $ExtractRoot | Out-Null
        Expand-Archive -Path $BackupPath -DestinationPath $ExtractRoot -Force

        $SqlPath = Get-ChildItem -Path $ExtractRoot -Recurse -File -Filter '*.sql' | Select-Object -First 1
        if (-not $SqlPath) {
            throw 'The backup archive does not contain a SQL dump.'
        }
    } else {
        $SqlPath = Get-Item $BackupPath
    }

    if ($DropExisting) {
        $ResetArgs = $CommonArgs + @('-e', "DROP DATABASE IF EXISTS $DbName; CREATE DATABASE $DbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
        & $Mysql @ResetArgs
        if ($LASTEXITCODE -ne 0) {
            throw 'Failed to recreate the database before restore.'
        }
    } else {
        $CreateArgs = $CommonArgs + @('-e', "CREATE DATABASE IF NOT EXISTS $DbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
        & $Mysql @CreateArgs
        if ($LASTEXITCODE -ne 0) {
            throw 'Failed to ensure the target database exists before restore.'
        }
    }

    Write-Host "Restoring database from $($SqlPath.FullName)"
    Get-Content -LiteralPath $SqlPath.FullName -Raw | & $Mysql @CommonArgs $DbName
    if ($LASTEXITCODE -ne 0) {
        throw 'Database restore failed.'
    }

    if ($IsZip) {
        $FilesRoot = Join-Path $ExtractRoot 'files'
        $Targets = @(
            @{ Source = Join-Path $FilesRoot 'uploads'; Destination = Join-Path $ProjectRoot 'uploads' },
            @{ Source = Join-Path $FilesRoot 'writable\uploads'; Destination = Join-Path $ProjectRoot 'writable\uploads' }
        )

        foreach ($target in $Targets) {
            if (Test-Path $target.Source) {
                New-Item -ItemType Directory -Force -Path $target.Destination | Out-Null
                Copy-Item -Path (Join-Path $target.Source '*') -Destination $target.Destination -Recurse -Force
            }
        }
    }

    Write-Host ''
    Write-Host 'Restore completed successfully.'
}
finally {
    if ($ExtractRoot -and (Test-Path $ExtractRoot)) {
        Remove-Item -Path $ExtractRoot -Recurse -Force
    }
}
