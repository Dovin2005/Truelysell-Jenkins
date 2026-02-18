<?php

namespace Modules\Advertisement\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ad_name' => 'required|string|max:255',
            'ad_type' => 'required|string|in:HTML,IMAGE',
            'body' => 'nullable|string|required_if:ad_type,HTML',
            'ad_position' => 'required|string',
            'ad_selector' => 'required|string',
            'ad_custom' => 'nullable|string',
            'status' => 'required|boolean',
            'ad_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048|required_if:ad_type,IMAGE',
            'ad_url' => 'nullable|string|required_if:ad_type,IMAGE',
            'ad_alt' => 'nullable|string|required_if:ad_type,IMAGE',
        ];
    }

    public function messages(): array
    {
        return [
            'ad_image.required_if' => 'The image field is required when ad type is IMAGE',
            'ad_url.required_if' => 'The URL field is required when ad type is IMAGE',
        ];
    }
}