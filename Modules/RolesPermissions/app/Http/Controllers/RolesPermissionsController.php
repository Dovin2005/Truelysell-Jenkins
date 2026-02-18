<?php

namespace Modules\RolesPermissions\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\RolesPermissions\app\Repositories\Contracts\RolesPermissionsRepositoryInterface;
use Modules\RolesPermissions\app\Http\Requests\RolesRequest;

class RolesPermissionsController extends Controller
{
    protected RolesPermissionsRepositoryInterface $rolesPermissionsRepository;

    public function __construct(RolesPermissionsRepositoryInterface $rolesPermissionsRepository)
    {
        $this->rolesPermissionsRepository = $rolesPermissionsRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->index($request);
        return response()->json($response, $response['code']);
    }

    public function store(RolesRequest $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->store($request);
        return response()->json($response, $response['code']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->destroy($request);
        return response()->json($response, $response['code']);
    }

    public function roleStatusChange(Request $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->roleStatusChange($request);
        return response()->json($response, $response['code']);
    }

    public function permissionList(Request $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->permissionList($request);
        return response()->json($response, $response['code']);
    }

    public function permissionUpdate(Request $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->permissionUpdate($request);
        return response()->json($response, $response['code']);
    }

    public function checkUniqueRoleName(Request $request): JsonResponse
    {
        $response = $this->rolesPermissionsRepository->checkUniqueRoleName($request);
        return response()->json($response);
    }
}