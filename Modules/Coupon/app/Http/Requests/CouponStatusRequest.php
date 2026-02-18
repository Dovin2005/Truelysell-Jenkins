<?php
namespace Modules\Coupon\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponStatusRequest extends FormRequest
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
            'id' => 'required|integer|exists:coupons,id',
            'status' => 'required|in:0,1',
            'language_code' => 'nullable|string|max:5',
        ];
    }
}