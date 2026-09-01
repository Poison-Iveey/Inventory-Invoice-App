<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
        $id = $this->route('customer');

        return [
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255','unique:customers,email,'.$id],
            'phone' => ['nullable','string','max:40'],
            'address' => ['nullable','string'],
        ];
    }
}
