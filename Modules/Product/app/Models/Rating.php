<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use SoftDeletes;

    protected $table = 'ratings';

    protected $fillable = ['id', 'user_id','product_id','parent_id','review','rating','review_date', 'booking_id'];

    public static function getProductRatingDetails($productId)
    {
        $averageRating = self::where('product_id', $productId)
            ->where('parent_id', 0)
            ->avg('rating'); // Get average rating
        $ratingCount = self::where('product_id', $productId)
            ->where('parent_id', 0)
            ->count(); // Get number of ratings

        return [
            'average_rating' => $averageRating ? round($averageRating, 1) : 0, // rounding to 1 decimal place
            'rating_count' => $ratingCount
        ];
    }

    public function reply()
    {
        return $this->hasOne(Rating::class, 'parent_id');
    }


}
