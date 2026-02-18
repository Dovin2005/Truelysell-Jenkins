<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactStoreRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    protected $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function store(ContactStoreRequest $request): JsonResponse
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'message' => $request->message
            ];

            $this->contactRepository->create($data);

            return response()->json([
                'code' => 200,
                'message' => __('Message sent successfully.'),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while sending message'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}