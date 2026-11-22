<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('plate')) {
            $this->merge(['plate' => strtoupper(preg_replace('/\s+/', ' ', trim($this->plate)))]);
        }
    }

    public function rules(): array
    {
        return [
            'slot_id' => ['required', Rule::exists('slots', 'id')->where('garage_id', $this->route('garage')->id)],
            'car_type' => ['required', 'string', 'max:100'],
            'plate' => ['required', 'string', 'max:15'],
            'period' => ['required', Rule::in(array_keys(Booking::PERIODS))],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'agree' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'agree.accepted' => 'Centang persetujuan Syarat & Ketentuan dan Privasi Data dulu.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
