<?php

namespace Modules\Leads\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormInputUserListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
        ];
    }
}