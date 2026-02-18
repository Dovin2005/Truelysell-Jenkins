<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->input('id'); // Get ID from request input

        return [
            'id' => 'required|exists:units,id',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'code')->ignore($unitId),
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
                function ($attribute, $value, $fail) use ($unitId) {
                    if ($value == $unitId) {
                        $fail('A unit cannot be its own base unit.');
                    }
                },
            ],
            'status' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Unit ID is required.',
            'id.exists' => 'The specified unit does not exist.',
            'code.required' => 'The unit code is required.',
            'code.unique' => 'This unit code is already taken.',
            'code.regex' => 'The unit code may only contain letters, numbers, dashes and underscores.',
            'name.required' => 'The unit name is required.',
            'base_unit_id.exists' => 'The selected base unit does not exist.',
            'status.required' => 'The status field is required.',
            'status.boolean' => 'The status must be a boolean value.',
        ];
    }
}