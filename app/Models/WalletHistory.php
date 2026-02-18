<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    use HasFactory;

    protected $table = 'wallet_history';

    // Mass assignable attributes
    protected $fillable = [
        'user_id',
        'amount',
        'payment_type',
        'status',
        'type',
        'reference_id',
        'transaction_id',
        'transaction_date',
    ];

    /**
     * Relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
