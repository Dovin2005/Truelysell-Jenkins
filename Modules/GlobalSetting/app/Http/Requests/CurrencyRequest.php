<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GlobalSetting\app\Models\AvailableCurrency;
use Modules\GlobalSetting\app\Models\Currency;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $availableCurrency = AvailableCurrency::find($this->input('available_currency_id'));

        return [
            'available_currency_id' => [
                'required',
                function ($attribute, $value, $fail) use ($availableCurrency) {
                    if (!$availableCurrency) {
                        return $fail(__('Currency not found'));
                    }

                    if (Currency::where('name', $availableCurrency->currency_name)
                        ->where('id', '!=', $this->route('id'))
                        ->exists()) {
                        return $fail(__('currency_exists'));
                    }
                }
            ],
            'is_default' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'available_currency_id.required' => __('currency_required'),
        ];
    }
}