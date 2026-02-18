<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * @property int $user_id
 * @property int  $ticket_id
 * @property string|null  $description
 * @property string|null  $status
 */
class TicketHistory extends Model
{
    use SoftDeletes;
    protected $table="ticket_history";
    protected $fillable = [
        'user_id',
        'ticket_id',
        'description',
        'status',
    ];
}
