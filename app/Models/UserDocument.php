<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $document
 * @property string|null $file_name
 * @property string|null $size
 * @property string|null $extension
 * @property string|array<string>|null $document_url
 * @property string|null $icon
 *
 */

class UserDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document'
    ];

    /**
    * @return BelongsTo<User, UserDocument>
    */
    public function user(): BelongsTo
    {
        /** @var belongsTo<User, UserDocument> */
        return $this->belongsTo(User::class, 'id');
    }
}
