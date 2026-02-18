<?php

namespace Modules\Service\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChangesHistory;

/**
 * Class Productmeta
 * 
 * @property int $id
 * @property int $product_id
 * @property string $source_key
 * @property string $source_Values
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */ 

class Productmeta extends Model
{
    use SoftDeletes;

    protected $table = 'products_meta';

    protected $fillable = ['id', 'product_id', 'source_key','source_Values', 'created_at', 'updated_at', 'deleted_at'];



}
