<?php

namespace Modules\Leads\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadsListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
            'provider_id' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3',
            'order_by' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|string',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1',
        ];
    }
}