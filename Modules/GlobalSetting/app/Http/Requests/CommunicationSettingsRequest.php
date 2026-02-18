<?php

namespace Modules\GlobalSetting\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommunicationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');
        
        return match($type) {
            'nexmo' => [
                'nexmo_api_key' => 'required|string',
                'nexmo_secret_key' => 'required|string',
                'nexmo_sender_id' => 'required|string',
            ],
            'twofactor' => [
                'twofactor_api_key' => 'required|string',
                'twofactor_secret_key' => 'required|string',
                'twofactor_sender_id' => 'required|string',
            ],
            'twilio' => [
                'twilio_api_key' => 'required|string',
                'twilio_secret_key' => 'required|string',
                'twilio_sender_id' => 'required|string',
            ],
            'smtp' => [
                'smtp_from_email' => 'required|email',
                'smtp_password' => 'required|string',
                'smtp_from_name' => 'required|string',
                'port' => 'required|integer',
                'host' => 'required|string',
            ],
            'phpmail' => [
                'phpmail_from_email' => 'required|email',
                'phpmail_password' => 'required|string',
                'phpmail_from_name' => 'required|string',
            ],
            'sendgrid' => [
                'sendgrid_from_email' => 'required|email',
                'sendgrid_key' => 'required|string',
            ],
            'fcm' => [
                'project_id' => 'required|string',
                'client_email' => 'required|email',
                'private_key' => 'required|string',
            ],
            default => [],
        };
    }
}