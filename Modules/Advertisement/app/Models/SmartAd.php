<?php

namespace Modules\Advertisement\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Advertisement\Database\Factories\SmartAdFactory;

class SmartAd extends Model
{
    use HasFactory;

    protected $table = 'smart_ads';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'body',
        'adType',
        'image',
        'imageUrl',
        'imageAlt',
        'views',
        'clicks',
        'enabled',
        'placements',
    ];
}
