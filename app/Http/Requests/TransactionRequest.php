<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'uploadPaymentProof' => [
                'booking_id' => 'required|exists:bookings,id',
                'payment_proof' => 'required|file|mimes:jpeg,png,gif,pdf|max:2048',
            ],
            'getProviderDetails' => [
                'provider_id' => 'required|integer|exists:users,id',
            ],
            'storePayoutHistory' => [
                'provider_id' => 'required|exists:users,id',
                'total_bookings' => 'required|integer',
                'total_earnings' => 'required|numeric',
                'admin_earnings' => 'required|numeric',
                'provider_pay_due' => 'required|numeric',
                'entered_amount' => 'required|numeric',
                'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ],
            'savePayouts' => [
                'provider_id' => 'required|exists:users,id',
                'payout_type' => 'required|integer|between:1,4',
                'paypal_id' => 'required_if:payout_type,1',
                'stripe_id' => 'required_if:payout_type,2',
                'holder_name' => 'required_if:payout_type,4',
                'bank_name' => 'required_if:payout_type,4',
                'account_number' => 'required_if:payout_type,4',
                'ifsc' => 'required_if:payout_type,4',
            ],
            'getPayoutDetails' => [
                'provider_id' => 'required|exists:users,id',
            ],
            'getProviderPayoutHistory' => [
                'provider_id' => 'required|exists:users,id',
            ],
              'listProviderRequest' => [],
            'updateProviderRequest' => [
                'provider_id' => 'required|exists:users,id',
                'provider_amount' => 'required|numeric|min:0',
                'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ],
            'sendProviderRequestAmount' => [
                'provider_id' => 'required|integer|exists:users,id',
                'amount' => 'required|numeric|min:1',
            ],
            'getProviderBalance' => [
                'provider_id' => 'required|exists:users,id',
            ],
            'userpayoutrequestlist' => [],
            'updaterefund' => [
                'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'bookingid' => 'required|exists:bookings,id',
            ],
            'listTransactions' => [
                'user_id' => 'nullable|integer|exists:users,id',
                'customer_id' => 'nullable|integer|exists:users,id',
                'provider_id' => 'nullable|integer|exists:users,id',
                'search' => 'nullable|string|max:255',
                'order_by' => 'nullable|in:asc,desc',
                'sort_by' => 'nullable',
            ],
        ];

        return $rules[$this->route()->getActionMethod()] ?? [];
    }

    public function messages(): array
    {
        return [
            'paypal_id.required_if' => 'Paypal ID is required.',
            'stripe_id.required_if' => 'Stripe ID is required.',
            'holder_name.required_if' => 'Holder name is required.',
            'bank_name.required_if' => 'Bank name is required.',
            'account_number.required_if' => 'Account number is required.',
            'ifsc.required_if' => 'IFSC is required.',
            'entered_amount.max' => 'The entered amount cannot be greater than the provider pay due amount.',
        ];
    }

    public function withValidator($validator)
    {
        if ($this->route()->getActionMethod() === 'storePayoutHistory') {
            $validator->after(function ($validator) {
                if ($this->entered_amount > $this->provider_pay_due) {
                    $validator->errors()->add('entered_amount', 'The entered amount cannot be greater than the provider pay due amount.');
                }
            });
        }
    }
}