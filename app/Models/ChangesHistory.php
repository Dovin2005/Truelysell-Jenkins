<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChangesHistory extends Model
{
    use HasFactory;

    protected $table = 'changes_history';

    protected $fillable = [
        'user_id',
        'type_id',
        'type',
        'changed_by',
        'field_name',
        'from_value',
        'to_value'
    ];
}
