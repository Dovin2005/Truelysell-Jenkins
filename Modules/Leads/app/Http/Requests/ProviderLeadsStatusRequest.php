<?php

namespace Modules\Leads\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderLeadsStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:provider_forms_input,id',
            'status' => 'required|integer|in:1,2,3',
            'user_form_inputs_id' => 'required|exists:user_form_inputs,id',
            'user_email' => 'nullable|email',
            'category_name' => 'nullable|string',
            'leads_data' => 'nullable|string',
            'provider_id' => 'nullable|exists:users,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}