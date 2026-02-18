<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_by' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|string|in:id,name,code,status',
            'search' => 'nullable|string',
        ];
    }
}