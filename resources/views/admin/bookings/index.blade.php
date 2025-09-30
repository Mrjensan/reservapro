@extends('layouts.app')

@section('title', 'Reservas')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Reservas</h1>
        <p class="text-muted mb-0">Gerencie status, exporte e filtre os agendamentos.</p>
    </div>
    <a href="{{ route('admin.bookings.export', request()->query()) }}" class="btn btn-outline-secondary">Exportar CSV</a>
</div>

<form method="GET" class="card shadow-sm mb-4">
    <div class="card-body row g-3">
        <div class="col-sm-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                @foreach ([
                    App\Models\Booking::STATUS_PENDING => 'Pendente',
                    App\Models\Booking::STATUS_CONFIRMED => 'Confirmada',
                    App\Models\Booking::STATUS_CANCELLED => 'Cancelada'
                ] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <label class="form-label">Servico</label>
            <select name="service_id" class="form-select">
                <option value="">Todos</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(($filters['service_id'] ?? '') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <label class="form-label">Data</label>
            <input type="date" name="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
        </div>
        <div class="col-sm-3">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control" placeholder="Cliente ou servico" value="{{ $filters['search'] ?? '' }}">
        </div>
    </div>
    <div class="card-footer text-end">
        <button class="btn btn-primary" type="submit">Filtrar</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Servico</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Responsavel</th>
                    <th class="text-end">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->start_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $booking->service?->name }}</td>
                        <td>
                            <div>{{ $booking->customer?->name }}</div>
                            <div class="text-muted small">{{ $booking->customer?->email }}</div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="d-flex align-items-center gap-2">
                                @csrf
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach ([
                                        App\Models\Booking::STATUS_PENDING => 'Pendente',
                                        App\Models\Booking::STATUS_CONFIRMED => 'Confirmada',
                                        App\Models\Booking::STATUS_CANCELLED => 'Cancelada'
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected($booking->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notify_customer" value="1">
                                    <label class="form-check-label small">Avisar</label>
                                </div>
                            </form>
                        </td>
                        <td>{{ $booking->user?->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary">Detalhes</a>
                            <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="d-inline" onsubmit="return confirm('Remover esta reserva?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Nenhuma reserva encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
