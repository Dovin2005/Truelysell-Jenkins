<?php
// Modules/Communication/app/Http/Requests/RegisterOtpRequest.php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'name' => 'required|string',
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'phone_number' => 'sometimes|string',
            'password' => 'sometimes|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}