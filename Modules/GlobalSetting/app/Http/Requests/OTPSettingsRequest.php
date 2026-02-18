<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTPSettingsRequest extends FormRequest
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
            'otp_type' => 'required_if:group_id,9',
            'otp_type.*' => 'required_if:group_id,9|in:sms,email',
            'otp_digit_limit' => 'required_if:group_id,9|integer|in:4,5,6',
            'otp_expire_time' => 'required_if:group_id,9|string|in:5 mins,2 mins,10 mins',
            'register' => 'nullable|boolean',
            'login' => 'nullable|boolean',
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
            'otp_type.required_if' => 'OTP type is required.',
            'otp_type.array' => 'OTP type must be an array.',
            'otp_type.*.in' => 'OTP type must be either SMS or Email.',
            'otp_digit_limit.required_if' => 'OTP digit limit is required.',
            'otp_digit_limit.in' => 'OTP digit limit must be 4, 5, or 6.',
            'otp_expire_time.required_if' => 'OTP expire time is required.',
            'otp_expire_time.in' => 'OTP expire time must be 5 mins, 2 mins, or 10 mins.',
            'register.boolean' => 'Register must be a boolean value.',
            'login.boolean' => 'Login must be a boolean value.',
        ];
    }
}
