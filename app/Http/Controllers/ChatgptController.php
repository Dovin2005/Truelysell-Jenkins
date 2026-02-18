<?php
namespace App\Http\Controllers;
use App\Services\OpenAIService;
use Illuminate\Http\Request;

class ChatgptController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $messages = [
            ['role' => 'user', 'content' => $request->message],
        ];

        try {
            $response = $this->openAIService->chat($messages);
           // print_r($response['choices']);
         //   $responseArray = json_decode($response, true);
            $content ="No content available.";
            if (isset($responseArray['choices']) && !empty($responseArray['choices'])) {
                $content = $response['choices'][0]['message']['content'];
            }
            $content = $response['choices'][0]['message']['content'];

            return response()->json(['code' => 200, 'message' => 'Data Fetched Successfully', 'data' => $content], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
