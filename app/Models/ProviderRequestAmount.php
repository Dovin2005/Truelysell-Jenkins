<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderRequestAmount extends Model
{
    use HasFactory;

    protected $table = 'provider_request_amount';

    protected $fillable = [
        'provider_id',
        'payment_id',
        'amount',
        'status',
    ];

    /**
     * Relationship with the User model (Provider).
     */
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Relationship with the Payment model.
     */
   
}
