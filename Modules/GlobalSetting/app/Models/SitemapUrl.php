<?php

namespace Modules\GlobalSetting\app\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $url
 * @property string|null $sitemap_path
 */
class SitemapUrl extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['url','sitemap_path'];
}
