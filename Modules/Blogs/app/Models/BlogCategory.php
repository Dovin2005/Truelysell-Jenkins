<?php

namespace Modules\Blogs\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'status', 'created_at', 'updated_at', 'language_id', 'parent_id'];

    /**
     * Get the posts for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Blogs\app\Models\BlogPost, \Modules\Blogs\app\Models\BlogCategory>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(\Modules\Blogs\app\Models\BlogPost::class, 'category');
    }

}
