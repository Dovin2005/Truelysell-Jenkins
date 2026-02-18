<?php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|string|in:admin,provider,user',
            'authid' => 'sometimes|exists:users,id',
        ];
    }
}