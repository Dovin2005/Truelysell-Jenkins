<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AwsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aws_access_key' => 'required|string',
            'aws_secret_access_key' => 'required|string',
            'aws_region' => 'required|string',
            'aws_bucket' => 'required|string',
            'aws_url' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'aws_access_key.required' => __('The AWS access key field is required.'),
            'aws_secret_access_key.required' => __('The AWS secret access key field is required.'),
            'aws_region.required' => __('The AWS region field is required.'),
            'aws_bucket.required' => __('The AWS bucket field is required.'),
            'aws_url.required' => __('The AWS URL field is required.'),
        ];
    }
}