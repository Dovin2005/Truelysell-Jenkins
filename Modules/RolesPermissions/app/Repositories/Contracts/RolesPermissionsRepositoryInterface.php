<?php

namespace Modules\RolesPermissions\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface RolesPermissionsRepositoryInterface
{
    public function index(Request $request): array;
    public function store(Request $request): array;
    public function destroy(Request $request): array;
    public function roleStatusChange(Request $request): array;
    public function permissionList(Request $request): array;
    public function permissionUpdate(Request $request): array;
    public function checkUniqueRoleName(Request $request): bool;
}
