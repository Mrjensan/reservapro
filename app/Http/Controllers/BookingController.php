<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('bookings.index', [
            'services' => $services,
            'businessHours' => config('booking.business_hours'),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ]);

        $start = Carbon::parse($data['start'], config('app.timezone'));
        $end = Carbon::parse($data['end'], config('app.timezone'));

        $bookings = Booking::query()
            ->with('service')
            ->whereBetween('start_at', [$start, $end])
            ->when($data['service_id'] ?? null, fn ($query, $serviceId) => $query->where('service_id', $serviceId))
            ->get();

        $events = $bookings->map(fn (Booking $booking) => [
            'id' => $booking->id,
            'title' => $booking->service?->name ?? 'Reserva',
            'start' => $booking->start_at->toIso8601String(),
            'end' => $booking->end_at->toIso8601String(),
            'status' => $booking->status,
        ]);

        return response()->json($events);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $service = $request->service();
        $start = $request->startAt();
        $end = $request->endAt();

        $booking = DB::transaction(function () use ($request, $service, $start, $end) {
            $customer = Customer::query()->updateOrCreate(
                ['email' => $request->input('email')],
                [
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                ]
            );

            return Booking::query()->create([
                'service_id' => $service->id,
                'customer_id' => $customer->id,
                'start_at' => $start,
                'end_at' => $end,
                'status' => Booking::STATUS_PENDING,
                'notes' => $request->input('notes'),
                'confirmation_code' => strtoupper(Str::random(8)),
            ])->load(['service', 'customer']);
        });

        $mail = new BookingConfirmationMail($booking);
        $mailer = Mail::to($booking->customer->email);

        if ($notification = config('booking.notification_email')) {
            $mailer->bcc($notification);
        }

        $mailer->send($mail);

        return redirect()
            ->route('home')
            ->with('status', 'Sua reserva foi registrada! Confirme os detalhes pelo e-mail enviado.');
    }
}
