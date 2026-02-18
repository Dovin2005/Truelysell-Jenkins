<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'user_id' => 'required|exists:users,id',
            'booking_date' => 'required|date_format:d-m-Y',
            'from_time' => 'required',
            'to_time' => 'required',
            'total_amount' => 'required|numeric',
            'branch_id' => 'sometimes|integer',
            'slot_id' => 'sometimes|integer',
            'user_city' => 'sometimes|string',
            'user_state' => 'sometimes|string',
            'note' => 'sometimes|string'
        ];
    }
}