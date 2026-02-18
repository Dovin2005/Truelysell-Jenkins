<?php
namespace Modules\Communication\app\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class OtpSetting
 *
 * @property int $id
 * @property string $email
 * @property string $otp
 * @property \Illuminate\Support\Carbon $expires_at
 */
class OtpSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'otp_settings';

    protected $fillable = ['email', 'otp', 'expires_at'];
}
