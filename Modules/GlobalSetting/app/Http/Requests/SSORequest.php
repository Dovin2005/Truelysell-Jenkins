<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SSORequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sso_client_id' => 'required|string',
            'sso_client_secret' => 'required|string',
            'sso_redirect_url' => 'required|url',
        ];
    }
}