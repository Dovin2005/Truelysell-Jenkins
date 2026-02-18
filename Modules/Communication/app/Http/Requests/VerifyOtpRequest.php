<?php
// Modules/Communication/app/Http/Requests/VerifyOtpRequest.php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'otp' => 'required|string',
        ];

        if ($this->login_type === 'provider_register') {
            $rules['email'] = 'required|email';
            $rules['name'] = 'required|string';
            $rules['password'] = 'required|string';
            $rules['category_id'] = 'required|integer';
            $rules['subcategory_ids'] = 'required|array';
            $rules['provider_first_name'] = 'required|string';
            $rules['provider_last_name'] = 'required|string';
        } elseif ($this->login_type === 'register') {
            $rules['email'] = 'required|email';
            $rules['name'] = 'required|string';
            $rules['password'] = 'required|string';
            $rules['first_name'] = 'required|string';
            $rules['last_name'] = 'required|string';
            $rules['phone_number'] = 'required';
        } elseif ($this->login_type === 'forgot_email') {
            $rules['forgot_email'] = 'required|email';
        } else {
            $rules['email'] = 'required|email';
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }
}