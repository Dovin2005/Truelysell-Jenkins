<?php

namespace Modules\Categories\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormInputRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [
            'id' => 'nullable|exists:category_form_inputs,id',
            'category_id' => 'required|exists:categories,id',
            'input_type' => 'required|string',
            'form_label' => 'required|string|max:255',
            'form_placeholder' => 'nullable|string|max:255',
            'form_description' => 'required|string|max:255',
            'is_required' => 'boolean',
            'options' => 'nullable',
            'file_size' => 'nullable|integer|min:1',  // Setting a minimum value for file size
            'has_other_option' => 'nullable',
        ];

        // Conditional validation for options
        if (in_array($this->input('input_type'), ['select', 'checkbox', 'radio'])) {
            $rules['options'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => __('The category ID is required.'),
            'category_id.exists' => __('The selected category ID is invalid.'),
            'input_type.required' => __('The input type is required.'),
            'form_label.required' => __('The form label is required.'),
            'form_label.max' => __('The form label cannot exceed 255 characters.'),
            'form_placeholder.max' => __('The form placeholder cannot exceed 255 characters.'),
            'form_description.required' => __('The form description is required.'),
            'form_description.max' => __('The form description cannot exceed 255 characters.'),
            'is_required.boolean' => __('The "is required" field must be true or false.'),
            'options.required' => __('Options are required for select, checkbox, and radio input types.'),
            'file_size.integer' => __('The file size must be an integer.'),
            'file_size.min' => __('The file size must be at least 1.'),
            'has_other_option.boolean' => __('The "has other option" field must be true or false.'),
        ];

    }

    /**
     * Custom attribute names for validation rules.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Category ID',
            'input_type' => 'Input Type',
            'form_label' => 'Form Label',
            'form_placeholder' => 'Form Placeholder',
            'form_description' => 'Form Description',
            'is_required' => 'Required Status',
            'options' => 'Options',
            'file_size' => 'File Size',
            'has_other_option' => 'Other Option',
        ];
    }

    /**
     * Add custom validation logic after initial validation rules.
     */
    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         // Custom validation logic can go here
    //         if ($this->input('input_type') === 'file' && !$this->filled('file_size')) {
    //             $validator->errors()->add('file_size', 'The file size is required when the input type is "file".');
    //         }
    //     });
    // }
}
