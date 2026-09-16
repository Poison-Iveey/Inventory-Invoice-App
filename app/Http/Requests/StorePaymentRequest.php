<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Payment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(['mpesa', 'card', 'paypal', 'stripe'])],
            'reference' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.required' => 'Please enter the transaction reference or confirmation code for your payment.',
        ];
    }
}
