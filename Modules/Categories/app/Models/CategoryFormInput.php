<?php

namespace Modules\Categories\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Add this for BelongsTo
use Modules\Categories\app\Models\Category; // Ensure Category is imported

/**
 * @property string|null $categories_id
 * @property string|null $type
 * @property string|null $label
 * @property string|null $placeholder
 * @property string|null $name
 * @property bool|null $is_required
 * @property string|null $options
 * @property string|null $file_size
 * @property string|null $other_option
 * @property int|null $order_no
 * @property int|null $language_id
 */
class CategoryFormInput extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'category_form_inputs';

    protected $fillable = [
        'categories_id',
        'type',
        'label',
        'placeholder',
        'name',
        'is_required',
        'options',
        'file_size',
        'other_option',
        'order_no',
    ];

    /**
     * Define the relationship between CategoryFormInput and Category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }
}
