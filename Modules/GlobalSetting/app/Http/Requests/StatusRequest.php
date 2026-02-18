<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sso_status' => 'nullable',
            'chatgpt_status' => 'nullable',
            'location_status' => 'nullable',
            'recaptcha_status' => 'nullable',
        ];
    }

    protected function statusFieldName(): string
    {
        return 'status';
    }
}