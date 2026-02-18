<?php

namespace Modules\Coupon\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'product_type',
        'category_id',
        'subcategory_id',
        'product_id',
        'coupon_type',
        'coupon_value',
        'quantity',
        'quantity_value',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

}
