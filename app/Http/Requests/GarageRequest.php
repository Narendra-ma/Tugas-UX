<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GarageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'size_note' => ['nullable', 'string', 'max:100'],
            'price_daily' => ['required', 'integer', 'min:0', 'max:10000000'],
            'price_weekly' => ['required', 'integer', 'min:0', 'max:50000000'],
            'price_monthly' => ['required', 'integer', 'min:0', 'max:100000000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
