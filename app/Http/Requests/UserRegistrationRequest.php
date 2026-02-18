<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'max:255',
                Rule::unique('users', 'name')->whereNull('deleted_at')
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],
            'password' => 'required|string|min:8',
            'phone_number' => 'required',
        ];

        if ($this->has('is_mobile') && $this->get('is_mobile') === "yes") {
            $rules['terms_policy'] = 'required|accepted';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'phone_number.required' => 'Phone Number is required.',
            'terms_policy.required' => 'You must accept the terms and conditions.',
            'terms_policy.accepted' => 'You must accept the terms and conditions.',
        ];
    }
}