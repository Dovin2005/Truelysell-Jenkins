<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BreadImageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required|integer',
            'bread_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}