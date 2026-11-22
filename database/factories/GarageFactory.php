<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GarageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->owner(),
            'name' => 'Garasi '.fake()->lastName(),
            'city' => fake()->randomElement(['Surabaya', 'Sidoarjo', 'Karangasem']),
            'address' => fake()->streetAddress(),
            'description' => null,
            'size_note' => null,
            'price_daily' => 30000,
            'price_weekly' => 150000,
            'price_monthly' => 500000,
            'is_active' => true,
        ];
    }
}
