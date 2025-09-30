@extends('layouts.app')

@section('title', 'Detalhes da reserva')

@section('content')
<a href="{{ route('admin.bookings.index') }}" class="btn btn-link mb-3">&larr; Voltar</a>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">{{ $booking->service?->name }}</h1>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Cliente</dt>
                    <dd class="col-sm-8">{{ $booking->customer?->name }}<br><span class="text-muted">{{ $booking->customer?->email }} | {{ $booking->customer?->phone }}</span></dd>
                    <dt class="col-sm-4">Periodo</dt>
                    <dd class="col-sm-8">{{ $booking->start_at->format('d/m/Y H:i') }} - {{ $booking->end_at->format('H:i') }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">{{ ucfirst($booking->status) }}</dd>
                    <dt class="col-sm-4">Responsavel</dt>
                    <dd class="col-sm-8">{{ $booking->user?->name ?? 'Nao atribuido' }}</dd>
                    <dt class="col-sm-4">Codigo</dt>
                    <dd class="col-sm-8">{{ $booking->confirmation_code }}</dd>
                </dl>
            </div>
        </div>
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Observacoes do cliente</h2>
                <p class="mb-0">{{ $booking->notes ?: 'Nenhuma anotacao.' }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Atualizar status</h2>
                <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach ([
                                App\Models\Booking::STATUS_PENDING => 'Pendente',
                                App\Models\Booking::STATUS_CONFIRMED => 'Confirmada',
                                App\Models\Booking::STATUS_CANCELLED => 'Cancelada'
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected($booking->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_customer" value="1">
                        <label class="form-check-label">Notificar cliente por e-mail</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar status</button>
                </form>
            </div>
        </div>
        <div class="card shadow-sm mt-4 border-danger">
            <div class="card-body">
                <h2 class="h5 text-danger">Acoes</h2>
                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta reserva?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Excluir reserva</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
