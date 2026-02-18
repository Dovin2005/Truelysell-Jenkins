<?php
namespace Modules\Communication\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 *@property string|null $totime
 * @property string|null $content
 * @property string|null $subject
 **/
class Templates extends Model
{
    use SoftDeletes;

    protected $table = 'templates';
    protected $fillable = ['type','title','subject','content','status', 'created_at', 'updated_at','created_by', 'updated_by'];
}
