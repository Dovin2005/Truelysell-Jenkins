<?php

namespace Modules\Communication\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Communication\app\Repositories\Contracts\EmailInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function sendEmail(Request $request): JsonResponse
    {
        $validatedData = $request->all();
        $emailRepository = app(EmailInterface::class);

        if (is_array($validatedData['to_email'])) {
            $response = $emailRepository->sendBulkEmail(
                $validatedData['to_email'],
                $validatedData
            );
        } else {
            $response = $emailRepository->sendEmail($validatedData);
        }

        return response()->json($response, $response['code']);
    }
}