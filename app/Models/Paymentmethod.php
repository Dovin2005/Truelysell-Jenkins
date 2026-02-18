<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Paymentmethod extends Model
{
    use SoftDeletes;

    protected $table = 'payment_methods';

    protected $fillable = ['id', 'payment_type', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by'];

}
