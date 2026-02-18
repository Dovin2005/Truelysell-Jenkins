<?php
namespace Modules\GlobalSetting\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * Class Templates
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $subject
 * @property string $content
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Templates extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 
        'notification_type',
        'title', 
        'subject', 
        'content', 
        'status', 
        'created_at', 
        'updated_at', 
        'created_by', 
        'updated_by'
    ];

    /**
     * Define any additional behavior or relationships here.
     */
}
