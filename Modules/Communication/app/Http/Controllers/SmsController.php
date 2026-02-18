<?php

namespace Modules\Communication\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Communication\app\Http\Requests\SendSmsRequest;
use Modules\Communication\app\Repositories\Contracts\SmsInterface;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    /**
     * Send an SMS message.
     *
     * @param SendSmsRequest $request
     * @return JsonResponse
     */
    public function sendSms(SendSmsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $smsRepository = app(SmsInterface::class);
        $response = $smsRepository->sendSms($validatedData);

        return response()->json($response, $response['code'] ?? 500);
    }
}