# ReservaPro

Projeto criado por Jensan para facilitar reservas de pequenos neg�cios. A aplica��o permite que clientes escolham hor�rios dispon�veis e que o time acompanhe tudo em um painel simples.

## Como funciona
- Agenda p�blica com calend�rio para ver e solicitar hor�rios.
- Painel administrativo para aprovar, cancelar e exportar reservas.
- E-mails autom�ticos para manter todo mundo informado.

Estamos desenvolvendo com carinho e ficamos felizes com sugest�es e contribui��es. Abra uma issue, envie um pull request ou mande uma ideia � toda ajuda � bem-vinda!
## Demo
- Execute `php artisan serve`
- Acesse `http://localhost:8000/demo` para navegar pela demonstra��o guiada
- Fa�a login com `admin@example.com` / `password` para o painel administrativo

## Deploy automatizado (Windows)
1. Copie o script `scripts/deploy-windows.ps1` para a VPS.
2. Abra PowerShell como administrador e execute:
   ```powershell
   Set-ExecutionPolicy RemoteSigned -Scope Process -Force
   .\deploy-windows.ps1 -MysqlRootPassword "SENHA_FORTE" -Port 80
   ```
3. Aguarde o t�rmino e acesse `http://SEU_IP/demo`.

Ajuste par�metros `-InstallDir`, `-RepoUrl` ou `-Port` se precisar.
