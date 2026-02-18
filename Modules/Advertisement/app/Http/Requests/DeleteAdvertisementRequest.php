<?php

namespace Modules\Advertisement\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:smart_ads,id'
        ];
    }
}