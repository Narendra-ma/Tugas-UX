<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SlotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:10',
                Rule::unique('slots', 'code')->where('garage_id', $this->route('garage')->id),
            ],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
