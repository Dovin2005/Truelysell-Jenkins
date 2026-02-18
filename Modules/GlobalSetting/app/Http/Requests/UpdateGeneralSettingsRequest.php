<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
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
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'group_id' => 'required|integer',
            'app_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'phone_no' => 'required|string|regex:/^[0-9]+$/',
            'site_email' => 'required|email',
            'fax_no' => 'required|string|max:50',
            'website' => 'required|string|max:255|url',
            'timezone' => 'required|string',
            'site_address' => 'required|string|max:255',
            'country' => 'required|integer',
            'state' => 'nullable|integer',
            'city' => 'nullable|integer',
            'postal_code' => 'required|string|max:20',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'group_id.required' => 'The group ID is required.',
            'app_name.required' => 'The application name is required.',
            'company_name.required' => 'The company name is required.',
            'phone_no.required' => 'The phone number is required.',
            'phone_no.regex' => 'The phone number must be numeric.',
            'site_email.required' => 'The site email is required.',
            'site_email.email' => 'The site email must be a valid email address.',
            'website.url' => 'The website must be a valid URL.',
            'postal_code.required' => 'The postal code is required.',
        ];
    }
}
