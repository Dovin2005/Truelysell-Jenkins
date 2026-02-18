<?php

namespace Modules\Advertisement\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'edit_id' => 'required|exists:smart_ads,id',
            'edit_ad_name' => 'required|string|max:255',
            'edit_ad_type' => 'required|string|in:HTML,IMAGE',
            'edit_body' => 'nullable|string|required_if:edit_ad_type,HTML',
            'edit_ad_position' => 'required|string',
            'edit_ad_selector' => 'required|string',
            'edit_ad_custom' => 'nullable|string',
            'edit_status' => 'required|boolean',
            'edit_ad_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'edit_ad_url' => 'nullable|string|required_if:edit_ad_type,IMAGE',
            'edit_ad_alt' => 'nullable|string|required_if:edit_ad_type,IMAGE',
        ];
    }

    public function messages(): array
    {
        return [
            'edit_ad_url.required_if' => 'The URL field is required when ad type is IMAGE',
        ];
    }
}