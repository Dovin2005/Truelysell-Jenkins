<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetStaffBookingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staffid' => 'required|integer|exists:users,id'
        ];
    }
}