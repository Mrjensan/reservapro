@extends('layouts.app')

@section('title', 'Demo - ReservaPro')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <h1 class="display-6 mb-3">Demo interativa</h1>
                <p class="lead text-muted">Esta página apresenta um resumo visual do fluxo da aplicação para você testar rapidamente.</p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a class="btn btn-primary btn-lg" href="{{ route('home') }}">Explorar agenda</a>
                    <a class="btn btn-outline-secondary btn-lg" href="{{ route('login') }}">Login administrativo</a>
                </div>
                <p class="text-muted">Utilize essas opções para navegar pelos módulos principais.</p>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">O que você encontra na demo</h2>
                <div class="vstack gap-3">
                    <div class="d-flex gap-3">
                        <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill pt-2 px-3">1</div>
                        <div>
                            <h3 class="h5 mb-1">Agenda pública</h3>
                            <p class="text-muted mb-0">Calendário em tempo real com FullCalendar. Visualize reservas por serviço e envie um pedido de horário.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill pt-2 px-3">2</div>
                        <div>
                            <h3 class="h5 mb-1">Painel administrativo</h3>
                            <p class="text-muted mb-0">Dashboard com métricas, ranking de serviços, próximos atendimentos e gestão completa de reservas.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="badge bg-primary-subtle text-primary-emphasis rounded-pill pt-2 px-3">3</div>
                        <div>
                            <h3 class="h5 mb-1">Gestão de serviços</h3>
                            <p class="text-muted mb-0">CRUD simples para criar ou pausar serviços, ajustar duração, preço e descrição.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Credenciais de teste</h2>
                <p class="text-muted">Use os dados gerados pelos seeders para acessar o painel:</p>
                <div class="bg-light rounded-3 p-3 mb-3">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">E-mail</dt>
                        <dd class="col-sm-8"><code>admin@example.com</code></dd>
                        <dt class="col-sm-4">Senha</dt>
                        <dd class="col-sm-8"><code>password</code></dd>
                    </dl>
                </div>
                <p class="mb-0 text-muted">Ao confirmar uma reserva, um e-mail automático será registrado no log (`storage/logs`).</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Próximos passos</h2>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><span class="badge bg-secondary me-2">Opcional</span> Configure SMTP real para enviar notificações.</li>
                    <li class="mb-2"><span class="badge bg-secondary me-2">Opcional</span> Ajuste horários comerciais via variáveis <code>BOOKING_*</code>.</li>
                    <li><span class="badge bg-secondary me-2">Opcional</span> Personalize layout, textos e traduções para o seu negócio.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
