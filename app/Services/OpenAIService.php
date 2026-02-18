<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
       $this->apiKey = config('services.openai.key'); 
       $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
    }

    public function chat(array $messages)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl, [
                'model' => 'gpt-4o', // Specify the model (e.g., gpt-3.5-turbo, gpt-4)
                'messages' => $messages,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Error communicating with OpenAI: ' . $response->body());

        throw new \Exception('Error communicating with OpenAI: ' . $response->body());
    }
}
