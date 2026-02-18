<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $id
 * @property string|null $provider
 * @property string|null $user
 *  @property string|null $admin_reply
 * @property int|null  $id
 * @property string|null  $subject
 * @property string|null  $content
 * @property string|null  $admin_reply
 * @property string|null  $status
 */
class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'description',
        'subject',
        'user_type',
        'reply_description',
        'status',
        'priority',
        'created_by','updated_by'
    ];
}
