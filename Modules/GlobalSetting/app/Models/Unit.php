<?php

namespace Modules\GlobalSetting\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'base_unit_id',
    ];

    /**
     * Get the base unit that this unit belongs to.
     */
    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get all units that use this unit as their base unit.
     */
    public function derivedUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    /**
     * Get display name for the unit.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->code . ')';
    }

    /**
     * Scope a query to only include units that can be used as base units.
     */
    public function scopeCanBeBaseUnit($query)
    {
        return $query->whereNull('base_unit_id');
    }
}