@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="h3 mb-4">Visao geral das reservas</h1>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <span class="text-muted text-uppercase small">Total</span>
                <h2 class="h4 mb-0">{{ $stats['total'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <span class="text-muted text-uppercase small">Confirmadas</span>
                <h2 class="h4 text-success mb-0">{{ $stats['confirmed'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <span class="text-muted text-uppercase small">Pendentes</span>
                <h2 class="h4 text-warning mb-0">{{ $stats['pending'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <span class="text-muted text-uppercase small">Canceladas</span>
                <h2 class="h4 text-danger mb-0">{{ $stats['cancelled'] }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Ocupacao proxima semana</h2>
                <p class="text-muted small">{{ $occupancy['range_start']->toFormattedDateString() }} ate {{ $occupancy['range_end']->toFormattedDateString() }}</p>
                <div class="progress mb-2" role="progressbar" aria-valuenow="{{ $occupancy['rate'] }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-primary" style="width: {{ $occupancy['rate'] }}%"></div>
                </div>
                <div class="d-flex justify-content-between text-muted small">
                    <span>{{ $occupancy['booked_minutes'] }} min reservados</span>
                    <span>Capacidade: {{ $occupancy['capacity_minutes'] }} min</span>
                </div>
                <div class="mt-4">
                    <canvas id="serviceChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Proximas reservas</h2>
                <div class="vstack gap-3">
                    @forelse ($upcoming as $booking)
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $booking->customer?->name }}</strong>
                                    <div class="text-muted small">{{ $booking->service?->name }}</div>
                                </div>
                                <span class="badge bg-light text-dark">{{ $booking->start_at->format('d/m H:i') }}</span>
                            </div>
                            <div class="text-muted small mt-1">Status: {{ ucfirst($booking->status) }}</div>
                        </div>
                    @empty
                        <p class="text-muted">Nenhuma reserva futura.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Servicos com melhor desempenho</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Servico</th>
                        <th>Reservas confirmadas (mes)</th>
                        <th>Duracao</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topServices as $service)
                        <tr>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->bookings_count }}</td>
                            <td>{{ $service->duration_minutes }} min</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">Cadastre ao menos um servico.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('serviceChart');
        if (!ctx) {
            return;
        }
        const labels = @json($topServices->pluck('name'));
        const data = @json($topServices->pluck('bookings_count'));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Reservas confirmadas',
                    data: data,
                    backgroundColor: '#0d6efd',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>
@endpush
