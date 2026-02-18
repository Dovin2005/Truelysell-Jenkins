<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBranch extends Model
{
    protected $table = 'service_branches';

    protected $fillable = [
        'service_id',
        'branch_id',
    ];
}
