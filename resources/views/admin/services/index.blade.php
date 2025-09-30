@extends('layouts.app')

@section('title', 'Servicos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Servicos</h1>
        <p class="text-muted mb-0">Cadastre e organize os servicos oferecidos.</p>
    </div>
    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">Novo servico</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Duracao</th>
                        <th>Valor</th>
                        <th>Reservas</th>
                        <th>Status</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->duration_minutes }} min</td>
                            <td>R$ {{ number_format($service->price, 2, ',', '.') }}</td>
                            <td>{{ $service->bookings_count }}</td>
                            <td>
                                @if ($service->is_active)
                                    <span class="badge bg-success-subtle border border-success-subtle text-success-emphasis">Ativo</span>
                                @else
                                    <span class="badge bg-secondary-subtle border border-secondary-subtle text-secondary-emphasis">Inativo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Remover este servico?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Nenhum servico cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
