<?php

namespace Modules\Leads\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
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
            'quote' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'description' => 'required|string',
            'provider_forms_inputs_id' => 'required|exists:provider_forms_input,id',
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
            'quote.required' => 'The quote amount is required.',
            'quote.numeric' => 'The quote must be a number.',
            'quote.min' => 'The quote must be at least 0.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'description.required' => 'The description is required.',
            'description.string' => 'The description must be a string.',
            'provider_forms_inputs_id.required' => 'The provider form input ID is required.',
            'provider_forms_inputs_id.exists' => 'The specified provider form input does not exist.',
        ];
    }
}