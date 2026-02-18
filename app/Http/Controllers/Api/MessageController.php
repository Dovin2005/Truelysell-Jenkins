<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    protected MessageRepositoryInterface $messageRepository;

    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function chatList(Request $request): JsonResponse
    {
        $response = $this->messageRepository->chatList($request);
        return response()->json($response);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $response = $this->messageRepository->sendMessage($request);
        return response()->json($response);
    }

    public function getMessages(Request $request): JsonResponse
    {
        $response = $this->messageRepository->getMessages($request);
        return response()->json($response);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $response = $this->messageRepository->searchUsers($request);
        return response()->json($response);
    }
}