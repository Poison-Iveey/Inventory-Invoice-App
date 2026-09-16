<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required','exists:customers,id'],
            'issue_date' => ['required','date'],
            'due_date' => ['required','date','after_or_equal:issue_date'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required','exists:products,id'],
            'items.*.quantity' => ['required','integer','min:1','max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Quantity is too large — please enter a realistic amount (up to 100,000).',
        ];
    }
}
