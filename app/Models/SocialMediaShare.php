<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMediaShare extends Model
{
    protected $fillable = [
        'platform_name',
        'url',
        'icon',
        'status',
    ];
}