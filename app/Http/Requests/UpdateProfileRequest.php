<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Chat\App\Models\User;

class UpdateProfileRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? auth()->id();
        $userType = $this->user_type ?? (User::find($userId)->user_type ?? null);

        $rules = [
            'email' => 'required|',
            'user_name' => 'required|max:255',
            'profile_image' => 'nullable|mimes:jpeg,jpg,png|max:2048',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'international_phone_number' => 'required',
            'gender' => 'required',
            'dob' => 'required|date',
            'bio' => 'nullable|string|max:5000',
            'address' => 'required|string|max:150',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
        ];

        if ($userType == 2) { // Provider
            $rules = array_merge($rules, [
                'company_image' => 'nullable|mimes:jpeg,jpg,png|max:2048',
                'company_name' => 'nullable|max:100',
                'company_address' => 'nullable|max:150',
                'company_website' => 'nullable|url',
            ]);
        } elseif ($userType == 4) { // Staff
            $rules = array_merge($rules, [
                'category' => 'required',
                'role_id' => 'required',
                'branch_id' => 'required|array',
                'status' => 'required|boolean',
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => __('email_required'),
            'email.unique' => __('email_exists'),
            'email.email' => __('email_format'),
            'user_name.required' => __('user_name_required'),
            'user_name.max' => __('user_name_maxlength'),
            'user_name.unique' => __('user_name_exists'),
            'profile_image.mimes' => __('image_extension'),
            'profile_image.max' => __('image_filesize'),
            'first_name.required' => __('first_name_required'),
            'first_name.max' => __('first_name_maxlength'),
            'last_name.required' => __('last_name_required'),
            'last_name.max' => __('last_name_maxlength'),
            'international_phone_number.required' => __('phone_number_required'),
            'gender.required' => __('gender_required'),
            'dob.required' => __('dob_required'),
            'address.required' => __('address_required'),
            'address.max' => __('address_maxlength'),
            'country.required' => __('country_required'),
            'state.required' => __('state_required'),
            'city.required' => __('city_required'),
            'postal_code.required' => __('postal_code_required'),
            'company_image.mimes' => __('image_extension'),
            'company_image.max' => __('image_filesize'),
            'company_name.max' => __('company_name_maxlength'),
            'company_address.max' => __('company_address_maxlength'),
            'company_website.url' => __('url_valid'),
            'category.required' => __('category_required'),
            'role_id.required' => __('Role is required.'),
            'branch_id.required' => __('Branch selection is required.'),
            'status.required' => __('Status is required.'),
        ];
    }
}