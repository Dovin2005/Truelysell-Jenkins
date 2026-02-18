<?php

namespace app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderSocialLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $providerId = auth()->user()->provider_id ?? auth()->id();
        $socialLinkId = $this->route('providerSocialLink') ? $this->route('providerSocialLink')->id : null;

        return [
            'social_link_id' => [
                'required',
                'exists:social_links,id',
                Rule::unique('provider_social_links', 'social_link_id')
                    ->where('provider_id', $providerId)
                    ->ignore($socialLinkId)
            ],
            'link' => [
                'required',
                'url',
                'max:255'
            ],
            'status' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'social_link_id.required' => 'Please select a social media platform.',
            'social_link_id.exists' => 'The selected platform is invalid.',
            'social_link_id.unique' => 'You already have a link for this platform.',
            'link.required' => 'Please provide a valid link.',
            'link.url' => 'Please provide a valid URL.',
            'link.max' => 'The link must not exceed 255 characters.',
            'status.boolean' => 'Status must be either active or inactive.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'social_link_id' => 'platform',
            'link' => 'social media link',
            'status' => 'status'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? true : false,
        ]);
    }
}