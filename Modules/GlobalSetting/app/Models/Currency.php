<?php

namespace Modules\GlobalSetting\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChangesHistory;

/**
 * @property string $updated_by
 * @property string $symbol
 *
 */
class Currency extends Model
{
    use SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = ['id', 'name', 'code', 'status', 'symbol', 'is_default', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'];

    protected $casts = [
        'symbol' => 'string',
    ];

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

    /**
     * Log the changes made to a currency record.
     *
     * @param Currency $currency_data
     * @param string $field
     * @param string $fromValue
     * @param string $toValue
     * @return void
     */
    public static function logChange(Currency $currency_data, string $field, string $fromValue, string $toValue): void
    {
        ChangesHistory::create([
            'user_id'    => auth()->user()->id ?? '1',
            'type_id'    => $currency_data->id,
            'type'       => 'currencies',
            'changed_by' => $currency_data->updated_by,
            'field_name' => ucfirst(str_replace('_', ' ', $field)),
            'from_value' => $fromValue,
            'to_value'   => $toValue,
        ]);
    }

    /**
     * Set the default currency.
     *
     * @param int $currencyId
     * @return void
     */
    public static function setDefault(int $currencyId): void
    {
        self::where('is_default', 1)->update(['is_default' => 0]);
        self::where('id', $currencyId)->update(['is_default' => 1]);
    }
}
