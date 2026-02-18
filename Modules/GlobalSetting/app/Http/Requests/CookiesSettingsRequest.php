<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CookiesSettingsRequest extends FormRequest
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
            'cookies_content_text' => 'required_if:group_id,10',
            'cookies_position' => 'required_if:group_id,10|string',
            'agree_button_text' => 'required_if:group_id,10|string|max:50',
            'decline_button_text' => 'required_if:group_id,10|string|max:50',
            'show_decline_button' => 'required_if:group_id,10|boolean',
            'lin_for_cookies_page' => 'required_if:group_id,10|max:255',
            'maintenance' => 'required_if:group_id,11',
            'maintenance_content' => 'required_if:group_id,11',
            'copyright' => 'required_if:group_id,8',
            'product' => 'nullable',
            'service' => 'nullable',
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
            'cookies_content_text.required_if' => 'Cookies content text is required.',
            'cookies_position.in' => 'Position must be either top or bottom.',
            'agree_button_text.required_if' => 'Agree button text is required.',
            'decline_button_text.required_if' => 'Decline button text is required.',
            'copyright.required_if' => 'Copyright Content is required.',
            'lin_for_cookies_page.required_if' => 'Link button text is required.',
            'decline_button_text.max' => 'Decline button text should not exceed 50 characters.',
            'lin_for_cookies_page.url' => 'The cookies page link must be a valid URL.',
        ];
    }
}
