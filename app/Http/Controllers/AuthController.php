<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function login(Request $request): JsonResponse
    {
        $response = $this->authRepository->login($request);
        return $response;
    }

    public function detail(): JsonResponse
    {
        $response = $this->authRepository->detail();
        return $response;
    }

    public function Userdetail(): JsonResponse
    {
        $response = $this->authRepository->Userdetail();
        return $response;
    }

    public function loginapi(Request $request): JsonResponse
    {
        $response = $this->authRepository->loginapi($request);
        return $response;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $response = $this->authRepository->register($request);
        return $response;
    }

    public function logout(Request $request)
    {
        $response = $this->authRepository->logout($request);
        return $response;
    }
}

