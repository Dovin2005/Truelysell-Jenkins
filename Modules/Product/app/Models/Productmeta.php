<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ChangesHistory;

/**
 * Class Productmeta
 *
 * @property int $id
 * @property int $product_id
 * @property string $source_key
 * @property string $source_Values
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */

class Productmeta extends Model
{
    use SoftDeletes;

    protected $table = 'products_meta';

    protected $fillable = [
        'id',
        'product_id',
        'source_key', // Add source_key here
        'source_Values', // Add source_Values here
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    public function showPrice() 
    {
        if(is_null($this->source_Values) || $this->source_Values=='') {
            return "";
        } else {
            $timed="";
            if($this->source_key=='Hourly')
            {
                $timed="/Hr";

            }
            if($this->source_key=='Minitue')
            {
                $timed="/Min";

            }
            if($this->source_key=='Minute')
            {
                $timed="/Min";

            }
            if($this->source_key=='Squre-metter')
            {
                $timed="/Sq-Mt";

            }
            if($this->source_key=='Square-feet')
            {
                $timed="/Sq-ft";
            }
            return $this->source_Values.$timed;

        }
    }

    public function showImage() {
        $image_source = asset('front/img/default-placeholder-image.png');
        if($this->source_Values=='' || is_null($this->source_Values))
        {
            $image_source = asset('front/img/default-placeholder-image.png');

        }else{
            if (file_exists(public_path('storage/' . $this->source_Values))) {
                $image_source = asset('storage/' . $this->source_Values);
            }

        }
        return $image_source;

    }

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
