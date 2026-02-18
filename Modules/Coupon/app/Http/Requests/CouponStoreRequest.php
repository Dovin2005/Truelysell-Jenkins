<?php
namespace Modules\Coupon\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponStoreRequest extends FormRequest
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
        $couponId = $this->input('id');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId)->whereNull('deleted_at')
            ],
            'product_type' => 'required|in:all,service,category,subcategory',
            'coupon_type' => 'required|in:fixed,percentage',
            'coupon_value' => 'required|numeric|min:0',
            'quantity' => 'required|in:unlimited,limited',
            'quantity_value' => 'required_if:quantity,limited|nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'product_id' => 'required_if:product_type,service|array',
            'product_id.*' => 'exists:products,id',
            'category_id' => 'required_if:product_type,category|array',
            'category_id.*' => 'exists:categories,id',
            'subcategory_id' => 'required_if:product_type,subcategory|array',
            'subcategory_id.*' => 'exists:categories,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => __('Coupon code is required.'),
            'code.unique' => __('This coupon code already exists.'),
            'product_type.required' => __('Product type is required.'),
            'coupon_type.required' => __('Coupon type is required.'),
            'coupon_value.required' => __('Coupon value is required.'),
            'quantity.required' => __('Quantity type is required.'),
            'quantity_value.required_if' => __('Quantity value is required when quantity is limited.'),
            'start_date.required' => __('Start date is required.'),
            'start_date.after_or_equal' => __('Start date must be today or later.'),
            'end_date.required' => __('End date is required.'),
            'end_date.after' => __('End date must be after start date.'),
            'product_id.required_if' => __('Products are required when product type is service.'),
            'category_id.required_if' => __('Categories are required when product type is category.'),
            'subcategory_id.required_if' => __('Subcategories are required when product type is subcategory.'),
        ];
    }
}