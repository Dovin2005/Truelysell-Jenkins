<?php
namespace Modules\Communication\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
class CommunicationSettings extends Model
{
    use SoftDeletes;

    protected $table = 'communication_settings';
    // Specify mass assignable fields
    protected $fillable = ['type','key','value','settings_type','created_at', 'updated_at','created_by', 'updated_by'];
}
