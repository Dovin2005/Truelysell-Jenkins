<?php

namespace Modules\Communication\app\Repositories\Eloquent;

use Modules\Communication\app\Repositories\Contracts\SmsInterface;
use Modules\Communication\app\Models\CommunicationSettings;
use Illuminate\Support\Facades\Http;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class SmsRepository implements SmsInterface
{
    public function sendSms(array $data): array
    {
        $settings = $this->getActiveSmsSettings();
        
        if (empty($settings)) {
            return ['status' => 'error', 'message' => __('No active SMS settings found.'), 'code' => 400];
        }

        if ($settings['provider'] === 'nexmo') {
            return $this->sendViaNexmo($settings, $data);
        }

        if ($settings['provider'] === 'twilio') {
            return $this->sendViaTwilio($settings, $data);
        }

        return ['status' => 'error', 'message' => __('Unsupported SMS provider.'), 'code' => 400];
    }

    public function getActiveSmsSettings(): array
    {
        $settings = CommunicationSettings::where('settings_type', 2)
            ->where(function($query) {
                $query->where('key', 'nexmo_status')
                      ->orWhere('key', 'twilio_status');
            })
            ->where('value', 1)
            ->first();

        if (!$settings) {
            return [];
        }

        $provider = str_replace('_status', '', $settings->key);
        $config = CommunicationSettings::where('settings_type', 2)
            ->where('type', $provider)
            ->pluck('value', 'key')
            ->toArray();

        return [
            'provider' => $provider,
            'config' => $config
        ];
    }

    protected function sendViaNexmo(array $settings, array $data): array
    {
        try {
            $basic = new Basic($settings['config']['nexmo_api_key'], $settings['config']['nexmo_secret_key']);
            $client = new Client($basic);

            $response = $client->sms()->send(
                new SMS($data['to_number'], $settings['config']['nexmo_sender_id'], $data['content'])
            );

            $message = $response->current();

            if ($message->getStatus() == 0) {
                return ['code' => 200, 'message' => __('Message sent successfully.'), 'data' => []];
            }

            return ['status' => 'error', 'message' => $message->getStatus(), 'code' => 500];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage(), 'code' => 500];
        }
    }

    protected function sendViaTwilio(array $settings, array $data): array
    {
        try {
            $response = Http::withBasicAuth(
                $settings['config']['twilio_api_key'], 
                $settings['config']['twilio_secret_key']
            )
            ->asForm()
            ->post('https://api.twilio.com/2010-04-01/Accounts/' . $settings['config']['twilio_api_key'] . '/Messages.json', [
                'From' => $settings['config']['twilio_sender_id'],
                'To' => $data['to_number'],
                'Body' => strip_tags($data['content']),
            ]);

            if ($response->successful()) {
                return ['code' => 200, 'message' => __('Message sent successfully.'), 'data' => []];
            }

            return ['status' => 'error', 'message' => $response->body(), 'code' => 500];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage(), 'code' => 500];
        }
    }
}