<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required|integer',
            'logo' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|max:512',
            'icon' => 'nullable|mimes:jpeg,png,jpg,svg|max:1024',
            'mobile_icon' => 'nullable|mimes:jpeg,png,jpg,svg|max:1024',
            'dark_logo' => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.max' => __('Logo size should not exceed 2MB.'),
            'favicon.max' => __('Favicon size should not exceed 512KB.'),
            'favicon.mimes' => __('Favicon must be in ICO or PNG format.'),
            'dark_logo.max' => __('Dark logo size should not exceed 2MB.'),
        ];
    }
}