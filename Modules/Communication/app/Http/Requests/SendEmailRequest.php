<?php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
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
            'to_email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'attachment' => 'nullable|string',
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
            'to_email.required' => 'Recipient email is required.',
            'to_email.email' => 'Please enter a valid email address.',
            'to_email.max' => 'Email address should not exceed 255 characters.',
            'subject.required' => 'Email subject is required.',
            'subject.string' => 'Subject must be a string.',
            'subject.max' => 'Subject should not exceed 255 characters.',
            'content.required' => 'Email content is required.',
            'content.string' => 'Content must be a string.',
            'attachment.string' => 'Attachment path must be a string.',
        ];
    }
}