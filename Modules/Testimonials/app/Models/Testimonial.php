<?php

namespace Modules\Testimonials\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Testimonial
 *
 * @property string|null $client_image
 * @property string $client_name
 * @property string $description
 * @property string $position
 * @property string $status
 * @property int $order_by
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */

class Testimonial extends Model
{
    use SoftDeletes;

    protected $table = 'testimonials';

    protected $fillable = ['client_name', 'description', 'client_image', 'position', 'status', 'order_by', 'deleted_at', 'created_at', 'updated_at'];

    public $timestamps = true;

    /**
     * Generate the file URL for the client image.
     *
     * @param string $file
     * @return string
     */
    public function file(string $file): string
    {
        return url('storage/testimonials') . '/' . $file;
    }


}
