<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|boolean',
            'user_type' => 'nullable|integer',
            'priority' => 'nullable|string',
            'user_id' => 'required|integer',
        ];
    }
}