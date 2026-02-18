<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetDefaultLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:languages,id',
            'type' => ['required', Rule::in(['default', 'status', 'rtl'])],
            'status' => 'required_if:type,status|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Language ID is required.',
            'id.exists' => 'Selected language does not exist.',
            'type.required' => 'Action type is required.',
            'type.in' => 'Invalid action type specified.',
            'status.required_if' => 'Status is required when changing language status.',
            'status.boolean' => 'Status must be a boolean value.',
        ];
    }
}