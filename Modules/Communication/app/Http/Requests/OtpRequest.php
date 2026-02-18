<?php
// Modules/Communication/app/Http/Requests/OtpRequest.php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'type' => 'sometimes|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}