# Deploy em Windows Server (sem interface gr�fica)

Guia r�pido para subir o projeto **ReservaPro** em uma VPS Windows (Datacenter/Core) sem ambiente gr�fico. Os comandos abaixo assumem que voc� est� conectado via RDP shell ou SSH e possui privil�gios de administrador.

## 1. Preparando o ambiente

### 1.1 Habilite o script remoto e atualize o PowerShell
```powershell
Set-ExecutionPolicy RemoteSigned -Scope LocalMachine -Force
Install-PackageProvider -Name NuGet -MinimumVersion 2.8.5.201 -Force
```

### 1.2 Instale o Chocolatey
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force;
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072;
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
refreshenv
```

### 1.3 Pacotes necess�rios
```powershell
choco install git php composer mysql -y
refreshenv
```
> Se preferir SQL Server, instale `sql-server-2019` ou configure um banco externo.

### 1.4 Configure o MySQL
```powershell
# Inicialize o servi�o
Start-Service mysql

# Defina senha root (substitua "SENHA_FORTE")
mysqladmin -u root password "SENHA_FORTE"

# Crie o banco
mysql -u root -p"SENHA_FORTE" -e "CREATE DATABASE reservapro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## 2. Publicando o c�digo

### 2.1 Clone o reposit�rio
```powershell
cd C:\Sites
git clone https://github.com/Mrjensan/reservapro.git
cd reservapro
```

### 2.2 Instale depend�ncias PHP
```powershell
composer install --no-dev --optimize-autoloader
```

### 2.3 Configure vari�veis de ambiente
```powershell
Copy-Item .env.example .env
php artisan key:generate
```
Edite o `.env` via PowerShell (`notepad` n�o existe; use `nano` se tiver ou `Set-Content`/`vim`). Exemplo r�pido:
```powershell
(Get-Content .env) -replace 'DB_PASSWORD=', 'DB_PASSWORD=SENHA_FORTE' | Set-Content .env
```
Ajuste `DB_*`, `BOOKING_*` e `MAIL_*` conforme necess�rio.

### 2.4 Rodar migra��es e seeders
```powershell
php artisan migrate --force --seed
```

## 3. Servindo a aplica��o

### Op��o A: servidor embutido + NSSM (mais simples)
1. Instale o NSSM:
    ```powershell
    choco install nssm -y
    ```
2. Crie um servi�o Windows que execute o servidor embutido do Laravel na porta 8000 (ou 80):
    ```powershell
    nssm install ReservaPro "C:\ProgramData\chocolatey\lib\php	ools\php.exe" "artisan" "serve" "--host=0.0.0.0" "--port=80"
    nssm set ReservaPro AppDirectory "C:\Siteseservapro"
    nssm set ReservaPro AppEnvironmentExtra "APP_ENV=production
APP_DEBUG=false"
    nssm start ReservaPro
    ```
3. Abra a porta no firewall:
    ```powershell
    New-NetFirewallRule -DisplayName "ReservaPro" -Direction Inbound -Action Allow -Protocol TCP -LocalPort 80
    ```

### Op��o B: IIS + FastCGI (mais robusto)
1. Instale IIS e m�dulos PHP:
    ```powershell
    Install-WindowsFeature Web-Server,Web-WebServer,Web-Common-Http,Web-Static-Content,Web-Default-Doc,Web-Http-Errors,Web-App-Dev,Web-ASP-Net45,Web-CGI,Web-ISAPI-Ext,Web-ISAPI-Filter,Web-Mgmt-Tools -IncludeAllSubFeature
    ```
2. Configure FastCGI para o execut�vel do PHP:
    ```powershell
    Import-Module WebAdministration
    $phpPath = "C:\Program Files\PHP\php.exe"  # ajuste conforme o caminho
    New-WebHandler -Path "*.php" -Verb "*" -Modules "FastCgiModule" -ScriptProcessor $phpPath -Name "PHP_via_FastCGI"
    ```
3. Aponte o site padr�o para `C:\Siteseservapro\public`:
    ```powershell
    Set-ItemProperty 'IIS:\Sites\Default Web Site' -name physicalPath -value 'C:\Siteseservapro\public'
    ```
4. Reinicie o IIS:
    ```powershell
iisreset
    ```
5. Garanta que o usu�rio do IIS tenha permiss�es de leitura e escrita em `storage` e `bootstrap/cache`:
    ```powershell
    icacls storage /grant "IIS_IUSRS:(OI)(CI)RW"
    icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)RW"
    ```

## 4. Processos em produ��o
- Configure `APP_ENV=production`, `APP_DEBUG=false` e um `APP_URL` correto.
- Utilize scheduler e queue se necess�rio:
    ```powershell
    # Scheduler (executar a cada minuto)
    schtasks /Create /SC MINUTE /MO 1 /TN ReservaProScheduler /TR "powershell -NoProfile -ExecutionPolicy Bypass -Command 'cd C:\Siteseservapro; php artisan schedule:run'"

    # Queue worker (com NSSM ou servi�o separado)
    nssm install ReservaProQueue "C:\ProgramData\chocolatey\lib\php	ools\php.exe" "artisan" "queue:work" "--tries=3"
    nssm start ReservaProQueue
    ```
- Configure logs e backups do banco conforme pol�ticas da VPS.

## 5. Teste final
Acesse `http://SEU_IP/demo` para conferir a p�gina de demonstra��o com links para a agenda e painel administrativo. Credenciais padr�o (seed): `admin@example.com` / `password`.

---
Feito isso, o projeto estar� rodando na VPS Windows sem depender de interface gr�fica.
