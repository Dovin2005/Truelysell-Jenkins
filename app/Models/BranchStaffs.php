<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchStaffs extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'staff_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }
}
