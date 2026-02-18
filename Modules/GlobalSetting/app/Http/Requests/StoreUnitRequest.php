<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:units,code',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'base_unit_id' => [
                'nullable',
                'exists:units,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'The unit code is required.',
            'code.unique' => 'This unit code is already taken.',
            'code.regex' => 'The unit code may only contain letters, numbers, dashes and underscores.',
            'name.required' => 'The unit name is required.',
            'base_unit_id.exists' => 'The selected base unit does not exist.',
        ];
    }
}