<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Library\CustomFailedValidation;

class AdminLoginRequest extends CustomFailedValidation
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;  // Allow the request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            //'password' => 'required|min:6',
            'password' => 'required',
            'remember' => 'boolean', // Add this line to include the remember option
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Enter a valid email address',
            'password.required' => 'Password is required',
            //'password.min' => 'Password must be at least 6 characters long',
        ];
    }

    public function validationData()
    {
        // Read input from JSON payload
        return $this->json()->all();
    }
}