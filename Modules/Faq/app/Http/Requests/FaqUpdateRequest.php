<?php

namespace Modules\Faq\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaqUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'edit_question' => 'required|string',
            'edit_answer' => 'required|string',
        ];
    }
}
