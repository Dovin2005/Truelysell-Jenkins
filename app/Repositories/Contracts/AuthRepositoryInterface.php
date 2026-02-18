<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
     public function detail(): JsonResponse;
     public function Userdetail(): JsonResponse;
     public function loginapi(Request $request): JsonResponse;
     public function register(Request $request): JsonResponse;
     public function logout(Request $request): JsonResponse;
}