<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\app\Models\TranslationLanguage;

class StoreLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Adjust as necessary for your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        $id = $this->input('id');

        return [
            'translation_language_id' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $translationLanguage = TranslationLanguage::find($value);
                    if (!$translationLanguage) {
                        return $fail(__('language_not_found'));
                    }

                    if (Language::where('code', $translationLanguage->code)
                        ->orWhere('name', $translationLanguage->name)
                        ->exists()) {
                        return $fail(__('language_exists'));
                    }
                }
            ],
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'translation_language_id.required' => __('language_required'),
            'translation_language_id.unique' => __('language_exists'),
        ];
    }
}
