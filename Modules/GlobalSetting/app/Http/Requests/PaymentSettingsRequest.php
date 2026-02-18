<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required|integer',
            'paypal_id' => 'nullable|string|max:255',
            'paypal_secret' => 'nullable|string|max:255',
            'paypal_live' => 'nullable|boolean',
            'stripe_key' => 'nullable|string|max:255',
            'stripe_secret' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'paypal_status' => 'nullable|boolean',
            'stripe_status' => 'nullable|boolean',
            'bank_status' => 'nullable|boolean',
            'wallet_status' => 'nullable|boolean',
        ];
    }
}
