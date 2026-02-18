<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomSettingsRequest extends FormRequest
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
            'group_id' => 'required|integer',
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
            'group_id.required' => 'Group ID is required.',
            'group_id.integer' => 'Group ID must be an integer.',
        ];
    }
}
