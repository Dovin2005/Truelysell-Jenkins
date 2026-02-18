<?php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'type' => 'required|string|in:/provider/dashboard,/admin/dashboard,/user/dashboard',
        ];
    }
}