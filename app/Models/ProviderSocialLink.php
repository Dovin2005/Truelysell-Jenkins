<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\GlobalSetting\app\Models\SocialLink;


class ProviderSocialLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provider_social_links';

    protected $fillable = [
        'provider_id',
        'social_link_id',
        'platform_name',
        'link',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the provider that owns the social link
     */
    public function provider()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the social link platform
     */
    public function socialLink()
    {
        return $this->belongsTo(SocialLink::class);
    }

    /**
     * Scope to get active social links
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get inactive social links
     */
    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function socialMedia()
    {
        return $this->belongsTo(SocialLink::class, 'social_link_id');
    }

}