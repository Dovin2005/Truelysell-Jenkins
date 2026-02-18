<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|integer',
            'status' => 'required|integer|in:1,2,3,4',
            'user_id' => 'nullable|integer',
            'assign_id' => 'nullable|integer',
            'type' => 'nullable|string|in:assignticket',
            'is_mobile' => 'nullable|string|in:yes',
            'auth_id' => 'nullable|integer',
        ];
    }
}