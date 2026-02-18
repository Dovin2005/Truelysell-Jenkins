<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CopyrightSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id' => 'required|integer|exists:languages,id',
            'group_id' => 'required|integer',
            'copyright' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'language_id.required' => __('Language selection is required.'),
            'copyright.required' => __('Copyright text is required.'),
            'copyright.max' => __('Copyright text cannot exceed 500 characters.'),
        ];
    }
}