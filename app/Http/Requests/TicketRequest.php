<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject' => 'required|string',
            'description' => 'required|string',
            'status' => 'nullable|boolean',
            'user_type' => 'nullable',
            'priority' => 'nullable',
            'user_id' => 'required|integer|exists:users,id'
        ];
    }
}