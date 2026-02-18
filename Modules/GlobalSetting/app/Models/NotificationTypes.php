<?php
namespace Modules\GlobalSetting\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
class NotificationTypes extends Model
{
    use SoftDeletes;

    protected $table = 'notification_types';
    protected $fillable = ['type','status','created_at', 'updated_at','created_by', 'updated_by'];
}
