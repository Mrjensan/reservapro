<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = Carbon::instance(fake()->dateTimeBetween('now', '+2 weeks'))->minute(0);

        return [
            'service_id' => Service::factory(),
            'customer_id' => Customer::factory(),
            'start_at' => $start,
            'end_at' => (clone $start)->addMinutes(60),
            'status' => fake()->randomElement([
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
            ]),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
