<?php

namespace Modules\Leads\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Payments extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';

    protected $fillable = ['id', 'payment_date', 'payment_type', 'user_type','user_id','amount','transaction_id','status', 'reference_id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by'];

}
