<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\app\Models\Product;
/**
 * @property int $id
 * @property string|null $provider
 * @property string|null $user
 *  @property string|null $admin_reply
 * @property int|null  $id
 * @property string|null  $subject
 * @property string|null  $content
 * @property string|null  $admin_reply
 * @property string|null  $status
 */
class Dispute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'booking_id',
        'product_id',
        'provider_id',
        'subject',
        'content',
        'admin_reply',
        'status',
    ];
    /**
     * Define a relationship to the User model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    /**
     * Define a relationship to the User model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }
    /**
     * Define a relationship to the Product model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    /**
     * Define a relationship to the Bookings model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Bookings::class, 'booking_id', 'id');
    }
}
