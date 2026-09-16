<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $managedUser = $this->route('user');

        return $managedUser instanceof User && ($this->user()?->can('update', $managedUser) ?? false);
    }

    public function rules(): array
    {
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($managedUser)],
            'role' => ['required', Rule::in(['admin', 'staff', 'accountant', 'customer'])],
            'customer_id' => ['nullable', 'exists:customers,id'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($this->input('role') !== 'customer' || ! $this->input('customer_id')) {
                return;
            }

            $customer = Customer::find($this->input('customer_id'));
            $managedUser = $this->route('user');

            if ($customer?->user_id && $customer->user_id !== $managedUser->id) {
                $validator->errors()->add('customer_id', 'This customer profile is already linked to another user.');
            }

            if ($managedUser->is($this->user()) && $this->input('role') !== 'admin') {
                $validator->errors()->add('role', 'You cannot remove your own administrator role.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('role') !== 'customer') {
            $this->merge(['customer_id' => null]);
        }
    }
}
