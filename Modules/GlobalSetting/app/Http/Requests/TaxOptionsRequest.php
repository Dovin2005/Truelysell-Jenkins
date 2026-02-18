<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use App\Library\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class TaxOptionsRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $taxTypeRule = 'required';

        if ($this->input('method') === 'add') {
            $taxTypeRule .= '|unique:general_settings,value';
        } elseif ($this->input('method') === 'update') {
            $taxTypeRule .= '|unique:general_settings,value,' . $this->tax_type_id . ',id';
        }

        return [
            'tax_type' => $taxTypeRule,
            'tax_rate' => 'required|integer',
            'group_id' => 'required',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tax_type.required' => __('truelysell_validation.form_validation_error.tax_type'),
            'tax_type.unique' => __('truelysell_validation.form_validation_error.tax_type_unique'),
            'tax_rate.required' => __('truelysell_validation.form_validation_error.tax_rate_required'),
            'tax_rate.integer' => __('truelysell_validation.form_validation_error.tax_rate_integer'),
            'group_id.required' => __('truelysell_validation.form_validation_error.group_id_required'),
        ];
    }

}
