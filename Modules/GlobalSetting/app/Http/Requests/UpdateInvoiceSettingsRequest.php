<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'invoice_logo' => 'nullable|max:2048',
            'invoice_prefix' => 'required|string|max:255',
            'invoice_starts' => 'required|integer',
            'providerlogo' => 'required',
            'group_id' => 'required|integer',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invoice_prefix.required' => __('Invoice prefix is required.'),
            'invoice_starts.required' => __('Invoice start number is required.'),
            'invoice_company_name.required' => __('Company name is required.'),
            'invoice_header_terms.required' => __('Invoice header terms are required.'),
            'invoice_footer_terms.required' => __('Invoice footer terms are required.'),
            'group_id.required' => __('Group ID is required.'),
        ];
    }
}
