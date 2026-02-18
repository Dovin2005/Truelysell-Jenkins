<?php

namespace Modules\Leads\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FormInputUserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
        ];
    }
}