<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChangesHistory;
use App\Models\User;
use Modules\Product\app\Models\Rating;
use Modules\Categories\app\Models\Categories;
use App\Models\Book;
use App\Models\Bookings;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = ['id', 'views','source_name','source_tag','slug', 'source_code','source_type','source_stock','source_description','tags','seo_title','source_price','source_brand','source_category','source_subcategory', 'source_description','status',  'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'];

   
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'product_id');
    }
    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'product_id');
    }

    public function showproductname() {
       
        return $this->source_name;

     }

}
