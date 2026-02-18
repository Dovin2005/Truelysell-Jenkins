<?php

namespace Modules\Blogs\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string|null $image
 * @property string $slug
 * @property string $category
 * @property string $description
 * @property string $popular
 * @property string $tags
 * @property string $seo_title
 * @property string $seo_description
 */
class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'image', 'category', 'description', 'popular', 'status', 'tags' ,'seo_title', 'seo_description', 'language_id', 'parent_id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by'];
    
    /**
     * Generate the file URL for the client image.
     *
     * @param string $file
     * @return string
     */
    public function file(string $file): string
    {
        return url('storage/blogs') . '/' . $file;
    }

    /**
     * Get the category that owns the blog post.
     *
     * @return BelongsTo<BlogCategory, BlogPost>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category');
    }

}
