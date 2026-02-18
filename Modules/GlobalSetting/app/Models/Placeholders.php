<?php
namespace Modules\GlobalSetting\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
class Placeholders extends Model
{
    use SoftDeletes;

    protected $table = 'placeholders';
    protected $fillable = ['type','key','value','settings_type','created_at', 'updated_at','created_by', 'updated_by'];
}
