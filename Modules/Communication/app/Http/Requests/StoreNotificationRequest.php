<?php

namespace Modules\Communication\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'communication_type' => 'required|integer',
            'source' => 'required|string',
            'reference_id' => 'required|integer',
            'user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
            'from_description' => 'required|string',
            'to_description' => 'required|string',
            'description' => 'sometimes|string',
        ];
    }
}