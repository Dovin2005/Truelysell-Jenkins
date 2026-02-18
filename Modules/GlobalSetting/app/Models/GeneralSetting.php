<?php

namespace Modules\GlobalSetting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class GeneralSetting extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // Uncomment and update the factory method if you have a custom factory.
    // protected static function newFactory(): GeneralSettingFactory
    // {
    //     return GeneralSettingFactory::new();
    // }

    /**
     * Scope a query to include the file URL.
     *
     * @param Builder $query
     * @param string $file
     * @return string
     */
    public function scopeFile(Builder $query, string $file): string
    {
        return url('storage/invoice-logos') . '/' . $file;
    }
}
