<?php

namespace Modules\Newsletter\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailSubscription extends Model
{
    use SoftDeletes;

    protected $table = 'email_subscriptions';

    protected $fillable = ['email', 'status', 'created_at', 'updated_at', 'deleted_at'];

    public $timestamps = true;

   
}
