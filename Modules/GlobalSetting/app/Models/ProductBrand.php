<?php

namespace Modules\GlobalSetting\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
    ];

    /**
     * Get the full URL for the brand image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return Storage::url($this->image);
    }

    /**
     * Delete the brand image file when the brand is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($brand) {
            if ($brand->image) {
                Storage::delete($brand->image);
            }
        });
    }
}