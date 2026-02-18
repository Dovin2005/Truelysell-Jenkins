<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 
use Illuminate\View\View;

interface AdminLoginRepositoryInterface
{
    public function login(Request $request): JsonResponse;
    public function userlogin(Request $request): JsonResponse;
    public function saveAdminDetails(Request $request): JsonResponse;
    public function getAdminDetails(Request $request): View|JsonResponse;
    public function changePassword(Request $request): JsonResponse;
}