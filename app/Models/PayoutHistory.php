<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 *
 * @property int $id
 * @property string $type
 * @property int $user_id
 * @property int $total_bookings
 * @property float $total_earnings
 * @property float $admin_earnings
 * @property float $pay_due
 * @property float $entered_amount
 * @property float $process_amount
 * @property string|null $payment_proof
 * @property float $remaining_amount
 */
class PayoutHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payout_history';

    protected $fillable = [
        'id',
        'type',
        'user_id',
        'total_bookings',
        'total_earnings',
        'admin_earnings',
        'pay_due',
        'entered_amount',
        'process_amount',
        'payment_proof',
        'remaining_amount',
        'payment_method'
    ];

    /**
     * The provider related to this payout history.
     *
     * @return BelongsTo<User, PayoutHistory>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
