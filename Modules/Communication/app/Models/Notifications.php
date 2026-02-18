<?php
namespace Modules\Communication\app\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
class Notifications extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';
    protected $fillable = ['communication_type','notification_type','reference_id','user_id','notification_date','description','read_type','notification_status','created_at', 'updated_at','created_by', 'updated_by'];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
        // or use 'user_id' if that's the sender
    }
}
