<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_by' => 'nullable|in:asc,desc',
            'sort_by' => 'nullable|string|in:id,name,code,status',
            'search' => 'nullable|string',
        ];
    }
}