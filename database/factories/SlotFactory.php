<?php

namespace Database\Factories;

use App\Models\Garage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'garage_id' => Garage::factory(),
            'code' => 'S'.fake()->unique()->numberBetween(1, 9999),
            'is_active' => true,
        ];
    }
}
