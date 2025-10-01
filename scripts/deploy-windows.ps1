param(
    [string]$InstallDir = 'C:\Sites\reservapro',
    [string]$RepoUrl = 'https://github.com/Mrjensan/reservapro.git',
    [string]$MysqlRootPassword = 'ChangeMe123!',
    [int]$Port = 80
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Info($message) {
    Write-Host "[INFO] $message" -ForegroundColor Cyan
}

function Assert-Admin {
    if (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] 'Administrator')) {
        throw 'Execute este script em um PowerShell elevado (Run as Administrator).'
    }
}

function Install-Chocolatey {
    if (Get-Command choco -ErrorAction SilentlyContinue) {
        return
    }

    Write-Info 'Instalando Chocolatey...'
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
}

function Refresh-Env {
    $refresh = Join-Path $env:ChocolateyInstall 'bin/RefreshEnv.cmd'
    if (Test-Path $refresh) {
        & $refresh | Out-Null
    }
}

function Ensure-Package($package) {
    if (choco list --local-only --exact $package | Select-String $package) {
        Write-Info "$package já instalado."
        return
    }

    Write-Info "Instalando $package..."
    choco install $package -y --no-progress
}

function Get-PhpPath {
    $phpExe = Get-Command php -ErrorAction SilentlyContinue
    if ($phpExe) {
        return $phpExe.Source
    }

    $candidate = 'C:\Program Files\PHP\php.exe'
    if (Test-Path $candidate) {
        return $candidate
    }

    $candidate = 'C:\tools\php80\php.exe'
    if (Test-Path $candidate) {
        return $candidate
    }

    throw 'php.exe não encontrado após instalação.'
}

function Get-ComposerPath {
    $composer = Get-Command composer -ErrorAction SilentlyContinue
    if ($composer) {
        return $composer.Source
    }

    $candidate = 'C:\ProgramData\ComposerSetup\bin\composer.exe'
    if (Test-Path $candidate) {
        return $candidate
    }

    throw 'composer.exe não encontrado após instalação.'
}

function Get-MySqlBin {
    $base = 'C:\Program Files\MySQL'
    if (-not (Test-Path $base)) {
        $base = 'C:\Program Files\MariaDB'
    }

    $mysql = Get-ChildItem -Path $base -Recurse -Filter 'mysql.exe' -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($mysql) {
        return $mysql.Directory.FullName
    }

    throw 'mysql.exe não encontrado. Verifique a instalação do MySQL.'
}

function Ensure-Nssm {
    $nssm = Get-Command nssm -ErrorAction SilentlyContinue
    if ($nssm) {
        return $nssm.Source
    }

    $path = 'C:\ProgramData\chocolatey\lib\nssm\tools\nssm.exe'
    if (Test-Path $path) {
        return $path
    }

    throw 'nssm.exe não encontrado.'
}

Assert-Admin
Install-Chocolatey
Refresh-Env

foreach ($pkg in @('git', 'php', 'composer', 'mysql', 'nssm')) {
    Ensure-Package $pkg
}

Refresh-Env

$phpPath = Get-PhpPath
$composerPath = Get-ComposerPath
$mysqlBin = Get-MySqlBin
$nssmPath = Ensure-Nssm

$mysqlService = Get-Service | Where-Object { $_.Name -match 'mysql' }
if (-not $mysqlService) {
    throw 'Serviço do MySQL não localizado. Reinicie o servidor ou reinstale o pacote.'
}

if ($mysqlService.Status -ne 'Running') {
    Write-Info 'Iniciando serviço MySQL...'
    Start-Service $mysqlService.Name
}

$mysqladmin = Join-Path $mysqlBin 'mysqladmin.exe'
$mysqlExe = Join-Path $mysqlBin 'mysql.exe'

try {
    & $mysqlExe -u root -e 'SELECT 1;' | Out-Null
    Write-Info 'MySQL root já possui senha. Pulando redefinição.'
} catch {
    Write-Info 'Definindo senha root do MySQL...'
    & $mysqladmin -u root password $MysqlRootPassword
}

Write-Info 'Garantindo banco de dados reservapro...'
& $mysqlExe -u root -p$MysqlRootPassword -e "CREATE DATABASE IF NOT EXISTS reservapro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if (-not (Test-Path $InstallDir)) {
    Write-Info "Criando diretório $InstallDir..."
    New-Item -ItemType Directory -Path $InstallDir | Out-Null
}

if (Test-Path (Join-Path $InstallDir '.git')) {
    Write-Info 'Repositório já existe, atualizando...'
    Push-Location $InstallDir
    git pull
    Pop-Location
} else {
    Write-Info 'Clonando repositório...'
    git clone $RepoUrl $InstallDir
}

Push-Location $InstallDir

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
}

Write-Info 'Atualizando .env com configurações básicas...'
$envContent = Get-Content '.env'
$envContent = $envContent -replace 'APP_ENV=local', 'APP_ENV=production'
$envContent = $envContent -replace 'APP_DEBUG=true', 'APP_DEBUG=false'
$envContent = $envContent -replace 'APP_URL=http://localhost', "APP_URL=http://$($env:COMPUTERNAME)"
$envContent = $envContent -replace 'DB_CONNECTION=sqlite', 'DB_CONNECTION=mysql'
$envContent = $envContent -replace '# DB_HOST=127.0.0.1', 'DB_HOST=127.0.0.1'
$envContent = $envContent -replace '# DB_PORT=3306', 'DB_PORT=3306'
$envContent = $envContent -replace '# DB_DATABASE=laravel', 'DB_DATABASE=reservapro'
$envContent = $envContent -replace '# DB_USERNAME=root', 'DB_USERNAME=root'
$envContent = $envContent -replace '# DB_PASSWORD=', "DB_PASSWORD=$MysqlRootPassword"
Set-Content '.env' $envContent

Write-Info 'Gerando APP_KEY...'
& $phpPath artisan key:generate --force

Write-Info 'Instalando dependências PHP...'
& $composerPath install --no-dev --optimize-autoloader

Write-Info 'Executando migrações e seeders...'
& $phpPath artisan migrate --force --seed

Write-Info 'Ajustando permissões em storage e cache...'
icacls storage /grant 'IIS_IUSRS:(OI)(CI)RW' /t | Out-Null
icacls 'bootstrap\cache' /grant 'IIS_IUSRS:(OI)(CI)RW' /t | Out-Null
icacls storage /grant 'NT AUTHORITY\NETWORK SERVICE:(OI)(CI)RW' /t | Out-Null
icacls 'bootstrap\cache' /grant 'NT AUTHORITY\NETWORK SERVICE:(OI)(CI)RW' /t | Out-Null

Write-Info 'Configurando serviço ReservaPro (PHP artisan serve)...'
$nssmService = 'ReservaPro'
& $nssmPath stop $nssmService 2>$null | Out-Null
& $nssmPath remove $nssmService confirm 2>$null | Out-Null

& $nssmPath install $nssmService $phpPath 'artisan' 'serve' --host=0.0.0.0 --port=$Port
& $nssmPath set $nssmService AppDirectory $InstallDir
& $nssmPath set $nssmService AppEnvironmentExtra "APP_ENV=production`nAPP_DEBUG=false"
& $nssmPath set $nssmService AppStdout (Join-Path $InstallDir 'storage\logseservapro-stdout.log')
& $nssmPath set $nssmService AppStderr (Join-Path $InstallDir 'storage\logseservapro-stderr.log')
& $nssmPath start $nssmService

Pop-Location

Write-Info 'Abrindo porta no firewall...'
New-NetFirewallRule -DisplayName 'ReservaPro' -Direction Inbound -Action Allow -Protocol TCP -LocalPort $Port -ErrorAction SilentlyContinue | Out-Null

Write-Info 'Deploy concluído.'
Write-Host "Acesse http://$(Invoke-RestMethod -Uri 'https://api.ipify.org?format=json').ip:$Port/demo" -ForegroundColor Green
