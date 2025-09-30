<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Mail\BookingStatusUpdatedMail;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'service_id', 'date', 'search']);

        $query = Booking::query()->with(['service', 'customer']);

        if (! empty($filters['status']) && in_array($filters['status'], [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_CANCELLED,
        ], true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['service_id'])) {
            $query->where('service_id', (int) $filters['service_id']);
        }

        if (! empty($filters['date'])) {
            try {
                $date = Carbon::parse($filters['date'], config('app.timezone'));
                $query->whereDate('start_at', $date);
            } catch (\Throwable $exception) {
                // ignore invalid date filters silently
            }
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($sub) use ($term) {
                $sub->whereHas('customer', function ($customerQuery) use ($term) {
                    $customerQuery->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                })->orWhereHas('service', function ($serviceQuery) use ($term) {
                    $serviceQuery->where('name', 'like', $term);
                });
            });
        }

        $bookings = $query->orderByDesc('start_at')
            ->paginate(15)
            ->withQueryString();

        $services = Service::query()->orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'services', 'filters'));
    }

    public function show(Booking $booking): View
    {
        $booking->loadMissing(['service', 'customer', 'user']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking): RedirectResponse
    {
        $status = $request->validated('status');
        $notify = $request->boolean('notify_customer');

        if ($status === Booking::STATUS_CONFIRMED) {
            $booking->user()->associate($request->user());
        } elseif ($status === Booking::STATUS_PENDING) {
            $booking->user()->dissociate();
        }

        $booking->status = $status;
        $booking->save();
        $booking->loadMissing(['customer', 'service']);

        if ($notify && $booking->customer?->email) {
            Mail::to($booking->customer->email)->send(new BookingStatusUpdatedMail($booking));
        }

        return back()->with('status', 'Status atualizado com sucesso.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', 'Reserva removida.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['status', 'service_id', 'date', 'search']);

        $query = Booking::query()->with(['service', 'customer']);

        if (! empty($filters['status']) && in_array($filters['status'], [
            Booking::STATUS_PENDING,
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_CANCELLED,
        ], true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['service_id'])) {
            $query->where('service_id', (int) $filters['service_id']);
        }

        if (! empty($filters['date'])) {
            try {
                $date = Carbon::parse($filters['date'], config('app.timezone'));
                $query->whereDate('start_at', $date);
            } catch (\Throwable $exception) {
                // ignore
            }
        }

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($sub) use ($term) {
                $sub->whereHas('customer', function ($customerQuery) use ($term) {
                    $customerQuery->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                })->orWhereHas('service', function ($serviceQuery) use ($term) {
                    $serviceQuery->where('name', 'like', $term);
                });
            });
        }

        $filename = 'reservas-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Servico',
                'Cliente',
                'Email',
                'Telefone',
                'Inicio',
                'Fim',
                'Status',
                'Responsavel',
                'Notas',
            ]);

            $query->orderBy('start_at')->chunk(200, function ($bookings) use ($handle) {
                foreach ($bookings as $booking) {
                    fputcsv($handle, [
                        $booking->id,
                        $booking->service?->name,
                        $booking->customer?->name,
                        $booking->customer?->email,
                        $booking->customer?->phone,
                        optional($booking->start_at)->toDateTimeString(),
                        optional($booking->end_at)->toDateTimeString(),
                        $booking->status,
                        $booking->user?->name,
                        $booking->notes,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
