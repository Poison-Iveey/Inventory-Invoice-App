<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'staff', 'accountant', 'customer'])],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->whereNull('user_id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('role') !== 'customer') {
            $this->merge(['customer_id' => null]);
        }
    }
}
