<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TerminateBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:200'],
            'arrears_amount' => ['required', 'integer', 'min:0', 'max:100000000'],
        ];
    }
}
