<?php

namespace Modules\Newsletter\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscriber_email' => 'required|email|unique:email_subscriptions,email',
        ];
    }

    public function messages(): array
    {
        return [
            'subscriber_email.required' => __('The email field is required.'),
            'subscriber_email.email' => __('Please provide a valid email address.'),
            'subscriber_email.unique' => __('This email is already subscribed.'),
        ];
    }
}
