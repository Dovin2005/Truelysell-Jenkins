<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketHistoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ticket_id' => 'required|integer',
            'description' => 'required|string',
            'user_id' => 'required|integer',
        ];
    }
}