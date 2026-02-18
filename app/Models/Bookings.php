<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Modules\Product\app\Models\Product;
use Modules\GoogleCalendarSync\Observers\BookingObserver;
use Modules\Product\app\Models\Rating;

/**
 * @property int $id
 * @property int|null $service_amount
 *  @property int|null $type
 * @property string|null $source_name
 * @property string|null $user_name
 *  @property string|null $provider_name
 * @property string|null $provideremail
 * @property string|null $user_email
 * @property string|null $user_address
 * @property string|null $booking_status_label
 * @property string|null $booking_status
 * @property int|null $refundid
 * @property string|null $paymenttype
 * @property \Carbon\Carbon|null $trxdate
 * @property \Carbon\Carbon|null $bookingdate
 * @property \Carbon\Carbon|null $booking_date
 * @property \Carbon\Carbon|null $fromtime
 * @property \Carbon\Carbon|null $totime
 * @property \Carbon\Carbon|null $refunddate
 */

class Bookings extends Model
{
    use SoftDeletes;

    protected $table = 'bookings';

    protected $fillable = ['id', 'order_id', 'product_id', 'branch_id', 'staff_id', 'slot_id', 'amount_tax', 'from_time', 'to_time', 'booking_date', 'booking_status', 'payment_type', 'user_id', 'service_amount', 'service_qty', 'notes', 'first_name', 'last_name', 'user_email', 'user_phone', 'user_city', 'user_state', 'user_postal', 'user_address', 'tranaction', 'total_amount', 'payment_status', 'payment_proof_path', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by', 'notes', 'additional_services', 'bank_transfer_proof', 'google_calendar_event_id', 'google_calendar_link'];
    protected $casts = [
        'payment_type' => 'string',
        'payment_status' => 'string',
        'service_amount' => 'float',
        'booking_status' => 'string',
        // Add other necessary casts here
    ];
    /**
     * Define a relationship to the Product model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
  /**
     * Define a relationship to the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function review()
    {
        return $this->hasOne(Rating::class, 'booking_id');
    }
}
