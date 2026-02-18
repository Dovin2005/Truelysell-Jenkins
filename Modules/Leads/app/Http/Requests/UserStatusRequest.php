<?php

namespace Modules\Leads\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStatusRequest extends FormRequest
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
        return [
            'id' => 'required|exists:user_form_inputs,id',
            'provider_forms_input' => 'required|exists:provider_forms_input,id',
            'status' => 'required|integer|in:2,3',
            'provider_email' => 'sometimes|email',
            'category_name' => 'sometimes|string',
            'quote_amount' => 'sometimes|numeric',
            'accepted_date' => 'sometimes|date',
            'user_name' => 'sometimes|string',
            'user_id' => 'sometimes|exists:users,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'id.required' => 'The user form input ID is required.',
            'id.exists' => 'The specified user form input does not exist.',
            'provider_forms_input.required' => 'The provider form input ID is required.',
            'provider_forms_input.exists' => 'The specified provider form input does not exist.',
            'status.required' => 'The status is required.',
            'status.integer' => 'The status must be an integer.',
            'status.in' => 'The status must be either 2 or 3.',
            'provider_email.email' => 'The provider email must be a valid email address.',
            'quote_amount.numeric' => 'The quote amount must be a number.',
            'accepted_date.date' => 'The accepted date must be a valid date.',
            'user_id.exists' => 'The specified user does not exist.'
        ];
    }
}