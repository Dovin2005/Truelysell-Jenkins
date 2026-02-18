<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceTemplateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>  // Explicitly defining the return type
     */
    public function rules(): array
    {
        return [
            'invoice_title' => [
                'required',
                'string',
                'max:255',
                $this->filled('template_id')
                    ? Rule::unique('invoice_templates', 'invoice_title')
                        ->ignore($this->template_id)
                        ->whereNull('deleted_at')
                    : Rule::unique('invoice_templates', 'invoice_title')
                        ->whereNull('deleted_at'),
            ],
            'invoice_type' => [
                'required',
                'string',
                $this->filled('template_id')
                    ? Rule::unique('invoice_templates', 'invoice_type')
                        ->ignore($this->template_id)
                        ->whereNull('deleted_at')
                    : Rule::unique('invoice_templates', 'invoice_type')
                        ->whereNull('deleted_at'),
            ],
            'template_content' => 'required',
            'template_id' => 'sometimes|exists:invoice_templates,id',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>  // Explicitly defining the return type
     */
    public function messages(): array
    {
        return [
            'invoice_title.required' => 'The invoice title is required.',
            'invoice_title.string' => 'The invoice title must be a valid string.',
            'invoice_title.max' => 'The invoice title cannot exceed 255 characters.',
            'invoice_title.unique' => 'The invoice title has already been taken. Please use a different title.',

            'template_content.required' => 'The template content is required.',

            'template_id.exists' => 'The selected template ID does not exist.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
