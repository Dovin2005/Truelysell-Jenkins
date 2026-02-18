<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use App\Library\CustomFailedValidation;
use Illuminate\Validation\Rule;

class StoreSocialLinkRequest extends CustomFailedValidation
{
    public function authorize(): bool
    {
        return true;
    }
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        $id = $this->route('id') ?? $this->input('id');
        return [
            'platform_name' => [
                'required',
                'min:3',
                'max:30',
                Rule::unique('social_links', 'platform_name')->ignore($id)->whereNull('deleted_at'),
            ],
            'link' => 'required|url',
            'icon' => 'required|min:3|max:30',
        ];
    }

    public function messages(): array
    {
        return [
            'platform_name.unique' => __('admin.general_settings.platform_name_unique'),
            'platform_name.required' => __('admin.general_settings.platform_required'),
            'platform_name.min' => __('admin.general_settings.platform_minlength'),
            'platform_name.max' => __('admin.general_settings.platform_maxlength'),
            'link.url' => __('admin.general_settings.enter_valid_url'),
            'link.required' => __('admin.general_settings.link_required'),
            'icon.required' => __('admin.general_settings.icon_required'),
            'icon.min' => __('admin.general_settings.icon_minlength'),
            'icon.max' => __('admin.general_settings.icon_maxlength'),
        ];
    }
}
