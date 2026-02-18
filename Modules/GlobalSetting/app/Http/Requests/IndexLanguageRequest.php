<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Change this if you need to handle authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>  // Explicitly defining the return type
     */
    public function rules(): array
    {
        return [
            'order_by' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|string|in:id,name,code,status',
            'search' => 'nullable|string',
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>  // Explicitly defining the return type
     */
    public function messages(): array
    {
        return [
            'order_by.in' => __('truelysell_validation.form_validation_error.order_by_in'),
            'sort_by.in' => __('truelysell_validation.form_validation_error.sort_by_in'),
            'search.string' => __('truelysell_validation.form_validation_error.search_string'),
        ];
    }
}
