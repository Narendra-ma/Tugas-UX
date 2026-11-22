<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.mimes' => 'Bukti bayar harus berupa JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran file maksimal 2 MB.',
        ];
    }
}
