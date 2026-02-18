<?php
// Modules/Communication/app/Http/Requests/ProviderRegisterOtpRequest.php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderRegisterOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'provider_name' => 'required|string',
            'provider_first_name' => 'sometimes|string',
            'provider_last_name' => 'sometimes|string',
            'phone_number' => 'sometimes|string',
            'password' => 'sometimes|string',
            'category_id' => 'sometimes|integer',
            'subcategory_ids' => 'sometimes|array',
            'company_name' => 'sometimes|string',
            'company_website' => 'sometimes|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}