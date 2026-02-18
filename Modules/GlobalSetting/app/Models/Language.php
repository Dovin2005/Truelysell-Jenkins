<?php

namespace Modules\GlobalSetting\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'direction',
        'status',
        'flag',
        'is_default',
    ];

    /**
     * Set the code attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = str_replace(' ', '-', strtolower($value));
    }

    /**
     * Set a language as the default.
     *
     * @param int $languageId
     * @return void
     */
    public static function setDefault(int $languageId): void
    {
        // Set all languages to not default
        self::where('is_default', 1)->update(['is_default' => 0]);

        // Set the specified language as default
        $language = self::findOrFail($languageId);
        $language->update(['is_default' => 1]);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('allLanguages');
        });

        static::created(function () {
            Cache::forget('allLanguages');
        });

        static::updated(function () {
            Cache::forget('allLanguages');
        });

        static::deleted(function () {
            Cache::forget('allLanguages');
        });
    }
}
