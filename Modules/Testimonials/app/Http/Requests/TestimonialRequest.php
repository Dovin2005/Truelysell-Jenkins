<?php

namespace Modules\Testimonials\app\Http\Requests;

use App\Library\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends CustomFailedValidation
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'client_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:0,1',
        ];
        
        if ($this->input('method') == 'add') {
            $rules['client_image'] = 'nullable|mimes:jpg,jpeg,png|max:2048';
        } else {
            $rules['id'] = 'required|exists:testimonials,id';
            $rules['client_image'] = 'nullable|mimes:jpg,jpeg,png|max:2048';
        }

        return $rules;
    }
}
