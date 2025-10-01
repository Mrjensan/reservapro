# ReservaPro

Projeto criado por Jensan para facilitar reservas de pequenos negócios. A aplicação permite que clientes escolham horários disponíveis e que o time acompanhe tudo em um painel simples.

## Como funciona
- Agenda pública com calendário para ver e solicitar horários.
- Painel administrativo para aprovar, cancelar e exportar reservas.
- E-mails automáticos para manter todo mundo informado.

Estamos desenvolvendo com carinho e ficamos felizes com sugestões e contribuições. Abra uma issue, envie um pull request ou mande uma ideia — toda ajuda é bem-vinda!
## Demo
- Execute `php artisan serve`
- Acesse `http://localhost:8000/demo` para navegar pela demonstração guiada
- Faça login com `admin@example.com` / `password` para o painel administrativo

## Deploy automatizado (Windows)
1. Copie o script `scripts/deploy-windows.ps1` para a VPS.
2. Abra PowerShell como administrador e execute:
   ```powershell
   Set-ExecutionPolicy RemoteSigned -Scope Process -Force
   .\deploy-windows.ps1 -MysqlRootPassword "SENHA_FORTE" -Port 80
   ```
3. Aguarde o término e acesse `http://SEU_IP/demo`.

Ajuste parâmetros `-InstallDir`, `-RepoUrl` ou `-Port` se precisar.
