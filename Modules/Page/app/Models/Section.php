<?php

namespace Modules\Page\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Section
 *
 * @property int $id
 * @property string $name
 * @property string|null $status
 * @property string|null $datas
 */
class Section extends Model
{
    use SoftDeletes;

    protected $fillable = ["name", "status", "datas"];
}
