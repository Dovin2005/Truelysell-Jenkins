<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecaptchaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recaptcha_api_key' => 'required|string',
            'recaptcha_secret_key' => 'required|string',
        ];
    }
}