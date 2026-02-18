<?php

namespace Modules\Page\app\Models;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{

    protected $fillable = [
        'footer_content',
        'status',
        'language_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];


}
