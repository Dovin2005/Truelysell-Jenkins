<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'category_id' => 'required|integer|exists:categories,id',
            'provider_first_name' => 'required',
            'provider_last_name' => 'required',
            'provider_name' => [
                'required',
                Rule::unique('users', 'name')->whereNull('deleted_at')
            ],
            'provider_email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],
            'provider_phone_number' => 'required|string|min:8',
            'provider_password' => 'required',
        ];

        if ($this->has('is_mobile') && $this->get('is_mobile') === "yes") {
            $rules['provider_terms_policy'] = 'required|accepted';
            $rules['company_website'] = 'nullable|url';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'provider_name.required' => 'Name is required.',
            'provider_email.required' => 'Email is required.',
            'provider_email.email' => 'Enter a valid email address.',
            'provider_email.unique' => 'This email is already registered.',
            'provider_password.required' => 'Password is required.',
            'provider_phone_number.required' => 'Phone Number is required.',
            'provider_terms_policy.required' => 'You must accept the terms and policy.',
            'company_website.url' => 'Enter a valid website URL.'
        ];
    }
}