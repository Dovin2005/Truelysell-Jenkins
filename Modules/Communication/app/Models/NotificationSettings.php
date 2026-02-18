<?php
namespace Modules\Communication\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
class NotificationSettings extends Model
{
    use SoftDeletes;

    protected $table = 'notification_settings';
    protected $fillable = ['type','source','is_flag','created_at', 'updated_at','created_by', 'updated_by'];
}
