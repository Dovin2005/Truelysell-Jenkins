<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use App\Library\CustomFailedValidation;

class AdminGlobalSettingRequest extends CustomFailedValidation
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
     * @return array<string, mixed>  // Explicitly defining the return type
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string|unique:general_settings,key|max:255',
            'value' => 'required|string|max:1000',
            'group_id' => 'required',
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
            'key.required' => __('truelysell_validation.form_validation_error.key_required'),
            'key.unique' => __('truelysell_validation.form_validation_error.key_unique'),
            'value.required' => __('truelysell_validation.form_validation_error.value_required'),
            'group-id.required' => __('truelysell_validation.form_validation_error.group_id_required'),
        ];
    }
}
