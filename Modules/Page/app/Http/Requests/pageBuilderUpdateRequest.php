<?php

namespace Modules\Page\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\GlobalSetting\app\Models\Language;

class pageBuilderUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
           'page_title' => 'required|max:100',
                'slug' => [
                    'required',
                    'max:100'
                ],
                'section_title' => 'nullable|array|min:1',
                'section_title.*' => 'nullable|string',
                'section_label' => 'nullable|array|min:1',
                'section_label.*' => 'nullable|string',
                'page_content' => 'nullable|array|min:1',
                'page_content.*' => 'nullable|string',
                'page_status' => 'nullable|array',
                'page_status.*' => 'boolean',
        ];
    }

    public function messages()
    {
        $langCode = Language::find($this->input('language_id'))->code ?? 'en';

        return  [
            'page_title.required' => __('Page title is required.', [], $langCode),
            'slug.required' => __('slug_required', [], $langCode),
        ];
    }
}