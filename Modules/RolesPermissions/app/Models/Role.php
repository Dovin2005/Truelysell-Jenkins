<?php

namespace Modules\RolesPermissions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\RolesPermissions\app\Models\Permission;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = ['role_name', 'status', 'created_by', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the permissions for the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Permission, Role>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'role_id');
    }

    /**
     * Boot the model's event handlers.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function ($role) {
            $role->permissions()->delete();
        });
    }

}
