<?php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSmsRequest extends FormRequest
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
            'to_number' => 'required|string|max:20',
            'content' => 'required|string|max:1600',
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
            'to_number.required' => 'Phone number is required.',
            'to_number.string' => 'Phone number must be a string.',
            'to_number.max' => 'Phone number should not exceed 20 characters.',
            'content.required' => 'SMS content is required.',
            'content.string' => 'Content must be a string.',
            'content.max' => 'Content should not exceed 1600 characters.',
        ];
    }
}