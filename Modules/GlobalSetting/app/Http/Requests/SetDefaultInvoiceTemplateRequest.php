<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetDefaultInvoiceTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:invoice_templates,id'
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The template ID is required.',
            'id.integer' => 'The template ID must be an integer.',
            'id.exists' => 'The selected template does not exist.'
        ];
    }
}