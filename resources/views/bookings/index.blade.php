@extends('layouts.app')

@section('title', 'Agenda online')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css">
<style>
    #calendar {
        min-height: 640px;
    }
    .fc-event-confirmed {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }
    .fc-event-pending {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #000 !important;
    }
    .fc-event-cancelled {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1">Agenda em tempo real</h2>
                        <p class="text-muted mb-0">Visualize os horarios confirmados e encontre o melhor momento para voce.</p>
                    </div>
                    <div>
                        <select id="calendarService" class="form-select">
                            <option value="">Todos os servicos</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h4 mb-3">Agende seu horario</h2>
                <form method="POST" action="{{ route('bookings.store') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label" for="service_id">Servico desejado</label>
                        <select name="service_id" id="service_id" class="form-select" required>
                            <option value="" selected disabled>Selecione</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" data-duration="{{ $service->duration_minutes }}">{{ $service->name }} ({{ $service->duration_minutes }} min)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="date">Data</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="time">Hora</label>
                            <input type="time" name="time" id="time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="name">Nome completo</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="phone">Telefone</label>
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="(11) 99999-9999">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label" for="notes">Observacoes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Compartilhe detalhes importantes para o atendimento"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Confirmar reserva</button>
                    <small class="text-muted">Funcionamento: {{ $businessHours['start'] ?? '09:00' }} ate {{ $businessHours['end'] ?? '18:00' }} (dias {{ implode(', ', $businessHours['days'] ?? []) }})</small>
                </form>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h5 mb-3">Servicos disponiveis</h3>
                <div class="vstack gap-3">
                    @forelse ($services as $service)
                        <div>
                            <h4 class="h6 mb-1">{{ $service->name }}</h4>
                            <p class="text-muted small mb-1">{{ $service->description }}</p>
                            <div class="d-flex gap-3 small">
                                <span><strong>DURACAO:</strong> {{ $service->duration_minutes }} min</span>
                                <span><strong>VALOR:</strong> R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Nenhum servico cadastrado ainda.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('calendar');
        const calendarService = document.getElementById('calendarService');
        const serviceSelect = document.getElementById('service_id');
        const dateInput = document.getElementById('date');
        const calendarRoute = {{ json_encode(route('bookings.calendar')) }};

        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'pt-br',
            slotMinTime: '{{ $businessHours['start'] ?? '08:00' }}',
            slotMaxTime: '{{ $businessHours['end'] ?? '20:00' }}',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            eventClassNames: info => ['fc-event-'+info.event.extendedProps.status],
            events: (info, success, failure) => {
                const params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr,
                });
                if (calendarService.value) {
                    params.append('service_id', calendarService.value);
                }
                fetch(`${calendarRoute}?${params.toString()}`)
                    .then(response => response.json())
                    .then(events => success(events))
                    .catch(error => failure(error));
            }
        });

        calendar.render();

        calendarService.addEventListener('change', () => {
            calendar.refetchEvents();
        });

        if (serviceSelect) {
            serviceSelect.addEventListener('change', () => {
                calendarService.value = serviceSelect.value;
                calendar.refetchEvents();
            });
        }
    });
</script>
@endpush
