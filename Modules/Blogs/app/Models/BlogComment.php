<?php

namespace Modules\Blogs\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $image
 * @property string $comment_date
 */
class BlogComment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'post_id',
        'name',
        'email',
        'image',
        'comment',
        'comment_date',
    ];
}
