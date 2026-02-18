<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChangesHistory;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'categories';

    protected $fillable = ['id', 'name'];

    public static function boot()
    {
        parent::boot();

        static::updating(function ($currency_data) {
            $original = $currency_data->getOriginal();

            if ($original['name'] != $currency_data->name) {
                self::logChange($currency_data, 'name', $original['name'], $currency_data->name);
            }

            if ($original['code'] != $currency_data->code) {
                self::logChange($currency_data, 'code', $original['code'], $currency_data->code);
            }

            if ($original['status'] != $currency_data->status) {
                self::logChange($currency_data, 'status', $original['status'], $currency_data->status);
            }

            if ($original['is_default'] != $currency_data->is_default) {
                self::logChange($currency_data, 'is_default', $original['is_default'], $currency_data->is_default);
            }
        });
    }

    public static function logChange($currency_data, $field, $fromValue, $toValue)
    {
        ChangesHistory::create([
            'user_id'    => auth()->user()->id?? '1',
            'type_id'    => $currency_data->id,
            'type'       => 'currencies',
            'changed_by' => $currency_data->updated_by,
            'field_name' => ucfirst(str_replace('_', ' ', $field)),
            'from_value' => $fromValue,
            'to_value'   => $toValue,
        ]);
    }

    public static function setDefault($currencyId)
    {
        self::where('is_default', 1)->update(['is_default' => 0, 'updated_by' => auth()->user()->id?? '1']);
        self::where('id', $currencyId)->update(['is_default' => 1, 'updated_by' => auth()->user()->id?? '1']);
    }

}
