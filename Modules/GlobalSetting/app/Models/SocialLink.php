<?php

namespace Modules\GlobalSetting\app\Models;

use App\Models\ProviderSocialLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'social_links';

    protected $fillable = [
        'platform_name',
        'link',
        'icon',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the provider social links for this platform
     */
    public function providerSocialLinks()
    {
        return $this->hasMany(ProviderSocialLink::class);
    }

    /**
     * Scope to get active social links
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}