<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DbBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_by' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|string',
            'search' => 'nullable|string',
        ];
    }
}