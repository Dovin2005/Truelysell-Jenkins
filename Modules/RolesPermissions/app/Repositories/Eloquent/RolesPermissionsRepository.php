<?php

namespace Modules\RolesPermissions\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\RolesPermissions\app\Models\Permission;
use Modules\RolesPermissions\app\Models\Role;
use Modules\RolesPermissions\app\Repositories\Contracts\RolesPermissionsRepositoryInterface;

class RolesPermissionsRepository implements RolesPermissionsRepositoryInterface
{
    public function index(Request $request): array
    {
        try {
            $userId = Auth::id() ?? $request->user_id;
            $orderBy = $request->order_by ?? 'asc';
            $status = $request->status ?? '';

            $data = Role::select('id', 'role_name', 'status', 'created_by')->where('created_by', $userId)->orderBy('id', $orderBy);

            if (request()->has('status') && $status == 1) {
                $data = $data->where('status', $request->status)->get();
            } else {
                $data = $data->get();
            }

            return [
                'code' => '200',
                'message' => __('Role details retrieved successfully.'),
                'data' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'code' => '500',
                'message' => __('An error occurred while retrieving Role.'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function store(Request $request): array
    {
        $createdBy = Auth::id() ?? $request->created_by;
        $id = $request->id ?? '';
        
        $data = [
            'role_name' => $request->role_name,
            'created_by' => $createdBy
        ];

        $successMessage = empty($id) ? __('Role created successfully.') : __('Role updated successfully.');
        $errorMessage = empty($id) ? __('Error! while creating role.') : __('Error! while updating role');

        try {
            Role::updateOrCreate(['id' => $id], $data);

            return [
                'code' => 200,
                'message' => $successMessage,
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => $errorMessage,
            ];
        }
    }

    public function destroy(Request $request): array
    {
        $id = $request->id;
        try {
            $role = Role::find($id);

            if (empty($role)) {
                return [
                    'code' => 404,
                    'success' => false,
                    'message' => __('Role not found.')
                ];
            }

            Role::where('id', $id)->delete();

            return [
                'code' => 200,
                'success' => true,
                'message' => __('Role deleted successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'success' => false,
                'message' => __('Error! while deleting role.')
            ];
        }
    }

    public function roleStatusChange(Request $request): array
    {
        $id = $request->id;
        $status = $request->status;
        try {
            $role = Role::find($id);

            if (empty($role)) {
                return [
                    'code' => 404,
                    'success' => false,
                    'message' => __('Role not found.')
                ];
            }

            Role::where('id', $id)->update([
                'status' => $status
            ]);

            return [
                'code' => 200,
                'message' => __('Role status updated successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while changing role status')
            ];
        }
    }

    public function permissionList(Request $request): array
    {
        $id = $request->id ?? $request->role_id;
        try {
            $orderBy = $request->order_by ?? 'asc';
            $data = Permission::where('role_id', $id)->orderBy('id', $orderBy)->get()->map(function ($permission) {
                
                return $permission;
            });

            $authId = Auth::id() ?? $request->user_id;
            $user = User::select('user_type')->where('id', $authId)->first();
        
            if (empty($data)) {
                $modules = DB::table('modules')->where('user_type', $user->user_type)->whereNull('deleted_at')->get();
    
                if (!empty($modules)) {
                    foreach ($modules as $module) {
                        $data->push(
                            ["id" => '',
                            "role_id" => $id,
                            "module" => $module->name,
                            "create" => 0,
                            "view" => 0,
                            "edit" => 0,
                            "delete" => 0,
                            "allow_all" => 0]);
                    }
                }

            } else {
                /** @var \Modules\RolesPermissions\app\Models\Role $role */
    
                $role = Role::with('permissions')->findOrFail($id);
                $permissions = $role->permissions->pluck('module')->toArray();
                
                $modules = DB::table('modules')->where('user_type', $user->user_type)->whereNull('deleted_at')->whereNotIn('name', $permissions)->get();
    
                if (!empty($modules)) {
                    foreach ($modules as $module) {
                        $data->push(
                            ["id" => '',
                            "role_id" => $id,
                            "module" => $module->name,
                            "create" => 0,
                            "view" => 0,
                            "edit" => 0,
                            "delete" => 0,
                            "allow_all" => 0]);
                    }
                }
            }

            return [
                'code' => '200',
                'message' => __('Permission details retrieved successfully.'),
                'data' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'code' => '500',
                'message' => __('An error occurred while retrieving Permissions.'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function permissionUpdate(Request $request): array
    {
        $roleId = $request->role_id;
        $permissions = $request->input('permissions', []);

        try {

            foreach ($permissions as $permission) {
                Permission::updateOrCreate(['id' => $permission['id'], 'role_id' => $roleId ], 
                    [
                        'module' => $permission['module'],
                        'create' => $permission['create'],
                        'view' => $permission['view'],
                        'edit' => $permission['edit'],
                        'delete' => $permission['delete'],
                        'allow_all' => $permission['allow_all']
                    ]);
            }

            return [
                'code' => 200,
                'message' => __('Permission updated successfully.'),
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('An error occurred while updating Permissions.'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function checkUniqueRoleName(Request $request): bool
    {
        $id = $request->input('id');
        $userId = $request->input('user_id');
        
        $validation = Validator::make($request->all(), [
            'role_name' => [
                Rule::unique('roles')->whereNull('deleted_at')->ignore($id)->where('created_by', $userId)
            ],
        ]);
        
        if ($validation->fails()) {
            return false;
        }

        return true;
    }
}