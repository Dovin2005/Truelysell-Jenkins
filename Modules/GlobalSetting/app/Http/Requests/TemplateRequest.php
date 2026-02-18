<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => 'required|in:1,2',
            'notification_type' => 'required|exists:notification_types,id',
            'title' => 'required|string|max:255',
            'status' => 'nullable|boolean'
        ];

        if ($this->input('type') == 1) {
            $rules['subject'] = 'required|max:255';
            $rules['content'] = 'required';
        } else {
            $rules['othercontent'] = 'required';
        }

        return $rules;
    }

    public function prepareForValidation()
    {
        if ($this->input('type') != 1) {
            $this->merge([
                'content' => $this->input('othercontent')
            ]);
        }
    }
}