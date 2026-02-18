<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayoutDetail extends Model
{
    use SoftDeletes;

    protected $table = 'provider_payout_details';

    protected $fillable = [
        'provider_id',
        'payout_type',
        'payout_detail',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id', 'id');
    }

    public function getStatusLabelAttribute()
    {
        return $this->status === 0 ? 'unpaid' : 'paid';
    }
}
