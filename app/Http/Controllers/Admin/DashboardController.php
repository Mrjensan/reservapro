<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\CarbonPeriod;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $stats = [
            'total' => Booking::query()->count(),
            'pending' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
            'confirmed' => Booking::query()->where('status', Booking::STATUS_CONFIRMED)->count(),
            'cancelled' => Booking::query()->where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        $occupancy = $this->occupancySummary();

        $topServices = Service::query()
            ->withCount(['bookings' => fn ($query) => $query
                ->where('status', Booking::STATUS_CONFIRMED)
                ->whereBetween('start_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
            ])
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        $upcoming = Booking::query()
            ->with(['service', 'customer'])
            ->where('start_at', '>=', $today)
            ->orderBy('start_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'occupancy' => $occupancy,
            'topServices' => $topServices,
            'upcoming' => $upcoming,
        ]);
    }

    protected function occupancySummary(): array
    {
        $business = config('booking.business_hours');
        $start = Carbon::now();
        $end = $start->copy()->addDays(7);
        $period = CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->endOfDay());

        $from = Carbon::createFromTimeString($business['start'] ?? '09:00');
        $to = Carbon::createFromTimeString($business['end'] ?? '18:00');
        $allowedDays = $business['days'] ?? [1, 2, 3, 4, 5];
        $minutesPerDay = max($from->diffInMinutes($to), 1);

        $activeServices = Service::query()->where('is_active', true)->count();
        $totalMinutes = 0;

        foreach ($period as $day) {
            if (in_array($day->dayOfWeekIso, $allowedDays, true)) {
                $totalMinutes += $minutesPerDay;
            }
        }

        $capacity = $totalMinutes * max($activeServices, 1);

        $bookedMinutes = Booking::query()
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->whereBetween('start_at', [$start, $end])
            ->get()
            ->sum(fn (Booking $booking) => $booking->start_at->diffInMinutes($booking->end_at));

        return [
            'range_start' => $start,
            'range_end' => $end,
            'capacity_minutes' => $capacity,
            'booked_minutes' => $bookedMinutes,
            'rate' => $capacity > 0 ? round(($bookedMinutes / $capacity) * 100, 1) : 0,
        ];
    }
}
