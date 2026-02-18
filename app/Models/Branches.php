<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branches extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'id',
        'branch_name',
        'branch_email',
        'branch_mobile',
        'branch_image',
        'branch_address',
        'branch_country',
        'lang',
        'lat',
        'branch_state',
        'branch_city',
        'branch_zip',
        'branch_startworkhour',
        'branch_endworkhour',
        'branch_workingday',
        'branch_holiday',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
