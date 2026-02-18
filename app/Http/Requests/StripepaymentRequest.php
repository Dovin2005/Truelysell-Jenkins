<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StripepaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'transaction_id' => 'required|exists:package_transactions,transaction_id',
        ];
    }

}