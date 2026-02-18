<?php
namespace Modules\GlobalSetting\Models;
use Illuminate\Database\Eloquent\Model;
class GeneralSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

     /**
     * Query scope to filter by file.
     *
     * @param string $file
     * @return string
     */
    public function scopeFile(string $file): String
    {
        return url('storage/invoice-logos') . '/' . $file;
    }
}
