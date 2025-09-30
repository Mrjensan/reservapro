<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone' => '+55 11 99999-9999',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $services = collect([
            [
                'name' => 'Consultoria de Marketing',
                'description' => 'Sessão de 1 hora para revisar estratégias e campanhas.',
                'duration_minutes' => 60,
                'price' => 250,
            ],
            [
                'name' => 'Aula Particular',
                'description' => 'Treinamento personalizado com material exclusivo.',
                'duration_minutes' => 90,
                'price' => 180,
            ],
            [
                'name' => 'Sessão de Mentoria',
                'description' => 'Mentoria para negócios locais focada em resultados rápidos.',
                'duration_minutes' => 45,
                'price' => 150,
            ],
        ])->map(fn (array $service) => Service::query()->updateOrCreate(
            ['name' => $service['name']],
            $service
        ));

        $customer = Customer::query()->updateOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'Cliente Demo',
                'phone' => '+55 11 98888-8888',
            ]
        );

        $demoDate = Carbon::now()->addDay()->setTime(10, 0);

        Booking::query()->updateOrCreate(
            [
                'service_id' => $services->first()->id,
                'customer_id' => $customer->id,
                'start_at' => $demoDate,
            ],
            [
                'end_at' => (clone $demoDate)->addMinutes($services->first()->duration_minutes),
                'status' => Booking::STATUS_CONFIRMED,
                'user_id' => $admin->id,
                'notes' => 'Reserva de demonstração criada pelo seeder.',
            ]
        );
    }
}
