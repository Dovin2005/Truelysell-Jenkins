<?php
namespace Modules\Chat\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int|null $user_id
 */
class Message extends Model
{
    use SoftDeletes;

    protected $table = 'message';
    protected $fillable = ['user_id','content','created_at', 'updated_at','created_by', 'updated_by'];
}
