<?php

namespace Modules\Service\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalService extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * 
     * @property int $id
     * @property int $provider_id
     * @property int $service_id
     * @property string $name
     * @property float $price
     * @property string $duration
     * @property string $image
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property \Illuminate\Support\Carbon|null $deleted_at
     */
    
    protected $fillable = [
        'provider_id',
        'service_id',
        'name',
        'price',
        'duration',
        'image',
    ];
}
