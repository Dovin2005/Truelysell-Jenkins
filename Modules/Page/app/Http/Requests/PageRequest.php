<?php

namespace Modules\Page\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
           'page_title' => 'required|max:100|unique:pages,page_title',
            'slug' => 'required|max:100|unique:pages,slug',
            'section_title' => 'nullable|array|min:1',
            'section_title.*' => 'nullable|string',
            'section_label' => 'nullable|array|min:1',
            'section_label.*' => 'nullable|string',
            'page_content' => 'nullable|array|min:1',
            'page_content.*' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'page_title.required' => __('The page title field is required.'),
            'slug.required' => __('The slug field is required.'),
            'slug.unique' => __('The slug has already been taken.'),
            'section_title.required' => __('At least one section title is required.'),
            'section_label.required' => __('At least one section label is required.'),
            'page_content.required' => __('At least one page content section is required.'),
            'section_title.*.required' => __('Each section title is required.'),
            'section_label.*.required' => __('Each section label is required.'),
            'page_content.*.required' => __('Each page content section is required.'),
        ];
    }
}