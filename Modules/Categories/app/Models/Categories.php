<?php

namespace Modules\Categories\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany for return type
use Modules\Product\app\Models\Product;

/**
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property string|null $source_type
 * @property string|null $image
 * @property string|null $icon
 * @property string|null $status
 * @property string|null $description
 * @property bool|null $featured
 * @property string|null $slug
 * @property string|null $created_at
 * @property string|null $created_by
 * @property string|null $updated_at
 * @property string|null $updated_by
 * @property string|null $deleted_at
 * @property string|null $deleted_by
 */
class Categories extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = ['id', 'name', 'parent_id','source_type', 'image', 'icon', 'status', 'description', 'featured', 'slug', 'language_id', 'parent_language_id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by'];

    /**
     * Define the relationship between Categories and SubCategories (self-referencing).
     *
     * @return HasMany
     */
    public function subCategory(): HasMany
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    /**
     * Define the relationship between Categories and Products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'source_category', 'id');
    }
    public function showcategoryname()
    {
        return $this->name;
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }
    
}
