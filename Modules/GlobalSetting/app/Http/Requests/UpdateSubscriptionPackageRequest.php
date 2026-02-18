<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSubscriptionPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'edit_id' => 'required|exists:subscription_packages,id',
            'edit_package_title' => 'required|string|max:255',
            'edit_price' => 'required|numeric|min:0',
            'edit_description' => 'required|string',
            'edit_subscription_type' => ['required', Rule::in(['regular', 'topup', 'promote'])],
            'status' => 'sometimes|boolean',
        ];

        if ($this->input('edit_subscription_type') === 'topup') {
            $rules = array_merge($rules, $this->getTopupRules());
        } else if ($this->input('edit_subscription_type') === 'promote') {
            $rules = array_merge($rules, $this->getPromoteRules());
        } else {
            $rules = array_merge($rules, $this->getRegularRules());
        }

        return $rules;
    }

    protected function getTopupRules(): array
    {
        return [
            'edit_package_term' => ['required', Rule::in(['day', 'month', 'yearly', 'lifetime'])],
            'edit_package_duration' => $this->getPackageDurationRule(),
            'edit_number_of_service' => 'required|integer|min:0',
            'edit_number_of_locations' => 'required|integer|min:0',
            'edit_number_of_staff' => 'required|integer|min:0',
        ];
    }

    protected function getPromoteRules(): array
    {
        return [
            'edit_package_term' => ['required', Rule::in(['day', 'month', 'yearly', 'lifetime'])],
            'edit_package_duration' => $this->getPackageDurationRule()
        ];
    }

    protected function getRegularRules(): array
    {
        return [
            'edit_package_term' => ['required', Rule::in(['day', 'month', 'yearly', 'lifetime'])],
            'edit_package_duration' => $this->getPackageDurationRule(),
            'edit_number_of_service' => 'required|integer|min:0',
            'edit_number_of_locations' => 'required|integer|min:0',
            'edit_number_of_staff' => 'required|integer|min:0',
        ];
    }

    protected function getPackageDurationRule(): string
    {
        $term = $this->input('edit_package_term');
        
        if ($term === 'day') {
            return 'required|integer|min:1|max:365';
        } elseif ($term === 'month') {
            return 'required|integer|min:1|max:12';
        }
        
        return 'sometimes|integer';
    }

    public function messages(): array
    {
        return [
            'edit_package_duration.max' => $this->input('edit_package_term') === 'day'
                ? 'Day should not be higher than 365.'
                : 'Month should not be higher than 12.',
            'edit_package_title.required' => 'The Package title is required.',
            'edit_price.required' => 'The Price field is required.',
            'edit_package_duration.required' => 'The package duration field is required.',
            'edit_number_of_service.required' => 'The number of service field is required.',
            'edit_number_of_feature_service.required' => 'The number of feature service field is required.',
            'edit_number_of_product.required' => 'The number of product field is required.',
            'edit_number_of_locations.required' => 'The number of locations field is required.',
            'edit_number_of_staff.required' => 'The number of staff field is required.',
            'edit_subscription_type.required' => 'Subscription Type is required.',
            'edit_description.required' => 'The description field is required.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json($validator->errors(), 422);

        throw new HttpResponseException($response);
    }
}