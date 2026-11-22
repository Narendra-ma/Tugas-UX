<?php

namespace Database\Factories;

use App\Models\Slot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'slot_id' => Slot::factory(),
            'car_type' => 'Avanza',
            'plate' => 'DK 1234 AB',
            'period' => 'monthly',
            'start_date' => today(),
            'end_date' => today()->addDays(29),
            'total_price' => 500000,
            'status' => 'menunggu_konfirmasi',
            'expires_at' => now()->addDay(),
            'agreed_at' => now(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function between(string $start, string $end): static
    {
        return $this->state(fn () => ['start_date' => $start, 'end_date' => $end]);
    }
}
