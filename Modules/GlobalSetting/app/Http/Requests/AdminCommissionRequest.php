<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use App\Library\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class AdminCommissionRequest extends CustomFailedValidation
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
        return [
            'commission_type' => 'required',
            'commission_rate' => 'required|integer',
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
            'commission_type.required' => __('truelysell_validation.form_validation_error.commission_type'),
            'commission_rate.required' => __('truelysell_validation.form_validation_error.commission_rate_required'),
            'commission_rate.integer' => __('truelysell_validation.form_validation_error.commission_rate_integer'),
            'group_id.required' => __('truelysell_validation.form_validation_error.group_id_required'),
        ];
    }

}
