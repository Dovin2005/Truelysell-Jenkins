<?php

namespace Modules\GlobalSetting\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class InvoiceTemplate extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['invoice_title', 'invoice_type', 'template_content', 'is_default'];

    // protected static function newFactory(): InvoiceTemplateFactory
    // {
    //     // return InvoiceTemplateFactory::new();
    // }
}
