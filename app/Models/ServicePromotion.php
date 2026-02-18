<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePromotion extends Model
{
    protected $fillable = [
        'provider_id',
        'service_id',
        'package_id',
        'amount',
        'payment_gateway',
        'payment_status',
        'transaction_id',
        'starts_at',
        'expires_at',
    ];

     protected $casts = [
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];
      public function service()
    {
        return $this->belongsTo(\Modules\Service\app\Models\Service::class, 'service_id');
    }
        public function package()
    {
        return $this->belongsTo(\Modules\GlobalSetting\app\Models\SubscriptionPackage::class, 'package_id');
    }
}