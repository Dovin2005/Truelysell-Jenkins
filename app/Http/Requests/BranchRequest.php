<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->input('id') ?? '';
        $userId = Auth::id() ?? $this->input('created_by');

        return [
            'email' => 'required|email',
            'branch_name' => [
                'required',
                'max:100',
                Rule::unique('branches', 'branch_name')
                    ->ignore($id)
                    ->whereNull('deleted_at')
                    ->where('created_by', $userId),
            ],
            'international_phone_number' => 'required',
            'branch_address' => 'required|string|max:150',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required|alpha_num|max:6',
            'start_hour' => 'required',
            'end_hour' => 'required',
            'working_day' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('email_required'),
            'email.email' => __('email_format'),
            'branch_name.required' => __('Branch name is required.'),
            'branch_name.max' => __('Branch name cannot exceed 100 characters.'),
            'branch_name.unique' => __('Branch name already exists.'),
            'branch_image.mimes' => __('image_extension'),
            'branch_image.max' => __('image_filesize'),
            'international_phone_number.required' => __('phone_number_required'),
            'international_phone_number.digits_between' => __('Phone number must be between 10 and 12 digits.'),
            'branch_address.required' => __('address_required'),
            'branch_address.max' => __('address_maxlength'),
            'country.required' => __('country_required'),
            'state.required' => __('state_required'),
            'city.required' => __('city_required'),
            'zip_code.required' => __('ZIP code is required.'),
            'zip_code.alpha_num' => __('ZIP code can only contain letters and numbers.'),
            'zip_code.max' => __('ZIP code cannot exceed 6 digits.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'code' => 422,
            'errors' => $validator->errors()->toArray(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
