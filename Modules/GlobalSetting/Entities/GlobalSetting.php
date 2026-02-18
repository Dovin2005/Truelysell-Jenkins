<?php

namespace Modules\GlobalSetting\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GlobalSetting
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property int|null $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class GlobalSetting extends Model
{
    protected $table = 'general_settings';
    protected $fillable = ['key', 'value', 'group_id', 'language_id'];

    /**
     * Generate a file URL for storage.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $file
     * @return string
     */
    public function scopeFile($query, $file)
    {
        return url('storage') . '/' . $file;
    }
    public function stipedetails() {
        return $this->value;

     }
    /**
     * Accessor for the value attribute.
     * Appends the storage URL for specific keys.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getValueAttribute($value)
    {
        if (in_array($this->key, ['logo', 'favicon', 'icon', 'dark_logo'])) {
            return url('storage/' . $value);
        }

        return $value;
    }
}
