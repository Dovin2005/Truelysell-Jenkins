<?php

namespace Modules\RolesPermissions\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\RolesPermissions\app\Models\Role;

class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = ['module', 'role_id', 'create', 'view', 'edit', 'delete', 'allow_all', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the role that owns the permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Role, Permission>
     */
    public function roles(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Accessor for the module attribute.
     *
     * @param string $value
     * @return string
     */
    public function getModuleAttribute(string $value): string
    {
        return __($value);  // Translating the module value
    }

}
