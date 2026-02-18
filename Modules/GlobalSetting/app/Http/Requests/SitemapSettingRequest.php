<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use App\Library\CustomFailedValidation;

class SitemapSettingRequest extends CustomFailedValidation
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {        
        return [
            'id'  => ['nullable', 'exists:sitemap_urls,id'],
            'url' => [
                'required',
                'max:255',
                $this->id
                    ? 'unique:sitemap_urls,url,' . $this->id . ',id'
                    : 'unique:sitemap_urls,url',
                'regex:/^(https?:\/\/)(localhost|(\d{1,3}\.){3}\d{1,3}|([a-zA-Z0-9.-]+\.[a-zA-Z]{2,}))(:\d+)?(\/.*)?$/'
            ]
        ];
    }

    public function messages()
    {
        $languageCode = $this->language_code ?? app()->getLocale();
        return [
            'url.required' => __('url_required', [], $languageCode),
            'url.regex' => __('url_valid', [], $languageCode),
            'url.max' => __('url_most_characters', [], $languageCode),
            'url.unique' => __('url_exists', [], $languageCode),
        ];
    }
}
