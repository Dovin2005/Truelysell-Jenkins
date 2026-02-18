<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStaff extends Model
{
    protected $table = 'service_staff';

    protected $fillable = [
        'service_branch_id',
        'staff_id',
    ];

    public function serviceBranch()
    {
        return $this->belongsTo(ServiceBranch::class);
    }
}
