<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:stripe,paypal,wallet',
            'user_id' => 'required|integer',
            'lead_id' => 'required|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least :min.',
            'payment_method.required' => 'The payment method field is required.',
            'payment_method.string' => 'The payment method must be a string.',
            'payment_method.in' => 'The selected payment method is invalid.',
            'user_id.required' => 'The user ID field is required.',
            'user_id.integer' => 'The user ID must be an integer.',
            'lead_id.required' => 'The lead ID field is required.',
            'lead_id.integer' => 'The lead ID must be an integer.',
        ];
    }
}