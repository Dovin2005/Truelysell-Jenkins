<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderDetail extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'subcategory_id',
    ];
}
