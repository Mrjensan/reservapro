<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    protected ?Carbon $startAt = null;
    protected ?Carbon $endAt = null;
    protected ?Service $service = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $service = Service::query()->find($this->integer('service_id'));

            if (! $service) {
                $validator->errors()->add('service_id', 'Servico nao encontrado.');

                return;
            }

            if (! $service->is_active) {
                $validator->errors()->add('service_id', 'Este servico esta temporariamente indisponivel.');

                return;
            }

            try {
                $start = Carbon::createFromFormat('Y-m-d H:i', $this->input('date').' '.$this->input('time'), config('app.timezone'));
            } catch (\Throwable $exception) {
                $validator->errors()->add('time', 'Informe um horario valido.');

                return;
            }

            $start->setSeconds(0);
            $end = (clone $start)->addMinutes($service->duration_minutes);

            if ($start->lessThan(now())) {
                $validator->errors()->add('date', 'Escolha um horario no futuro.');
            }

            $businessHours = config('booking.business_hours');
            $allowedDays = $businessHours['days'] ?? [];

            if (! empty($allowedDays) && ! in_array($start->dayOfWeekIso, $allowedDays, true)) {
                $validator->errors()->add('date', 'A empresa nao atende neste dia.');
            }

            $from = $businessHours['start'] ?? '09:00';
            $to = $businessHours['end'] ?? '18:00';

            if ($start->format('H:i') < $from || $end->format('H:i') > $to) {
                $validator->errors()->add('time', 'Selecione um horario dentro do expediente.');
            }

            if (! $validator->errors()->hasAny(['date', 'time'])) {
                if (Booking::hasOverlap($service->id, $start, $end)) {
                    $validator->errors()->add('time', 'Este horario ja esta reservado.');
                }
            }

            $this->service = $service;
            $this->startAt = $start;
            $this->endAt = $end;
        });
    }

    public function startAt(): Carbon
    {
        return $this->startAt ?? Carbon::now();
    }

    public function endAt(): Carbon
    {
        return $this->endAt ?? Carbon::now();
    }

    public function service(): ?Service
    {
        return $this->service;
    }
}
