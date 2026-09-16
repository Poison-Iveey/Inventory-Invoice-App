<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'price' => ['required','numeric','min:0.01','max:99999999.99'],
            'stock' => ['required','integer','min:0','max:2147483647'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'image' => ['nullable','image','max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'Price must be greater than zero.',
            'price.max' => 'Price is too large — please enter a realistic amount (up to 99,999,999.99).',
            'stock.max' => 'Stock is too large to store.',
        ];
    }
}
