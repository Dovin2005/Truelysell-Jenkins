<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChangesHistory;

/**
 * Class Service
 * 
 * @property int $id
 * @property int $user_id
 * @property string $source_name
 * @property string $slug
 * @property string $source_code
 * @property string $source_type
 * @property string $source_tag
 * @property string $source_description
 * @property string $source_category
 * @property string $source_subcategory
 * @property float $source_price
 * @property string $plan
 * @property string $price_description
 * @property string $source_brand
 * @property int $source_stock
 * @property string $seo_title
 * @property string $tags
 * @property bool $featured
 * @property bool $popular
 * @property string $seo_description
 * @property string $price_type
 * @property string $duration
 * @property string $country
 * @property string $state
 * @property string $city
 * @property string $address
 * @property string $pincode
 * @property string $include
 * @property string $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */ 

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'id',
        'user_id',
        'source_name',
        'slug',
        'source_code',
        'source_type',
        'source_description',
        'source_category',
        'source_subcategory',
        'source_price',
        'plan',
        'price_description',
        'source_brand',
        'source_stock',
        'seo_title',
        'tags',
        'featured',
        'popular',
        'seo_description',
        'price_type',
        'duration',
        'country',
        'state',
        'city',
        'address',
        'pincode',
        'include',
        'status',
        'created_by',
    ];

   

}
