<?php

namespace Modules\Page\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Page
 *
 * @property int $id
 * @property string $page_title
 * @property string $slug
 * @property string|null $page_content
 * @property string|null $about_us
 * @property string|null $terms_conditions
 * @property string|null $privacy_policy
 * @property string|null $contact_us
 * @property string|null $seo_tag
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property int|null $language_id
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Page extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'language_id',
        'parent_id',
        'page_title',
        'slug',
        'page_content',
        'about_us',
        'terms_conditions',
        'privacy_policy',
        'contact_us',
        'seo_tag',
        'seo_title',
        'seo_description',
        'status',
    ];

    public static string $pageSecretKey = 'pageId';

    /**
     * Get the encrypted ID.
     *
     * @return string
     */
    public function getEncryptedIdAttribute(): string
    {
        return encrypt($this->id);
    }
}
