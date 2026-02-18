<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Import the trait

class Administrator extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens; // Use the trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     /**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $password
 * @property string|null $phone_number
 * @property string|null $user_type
 * @property int|null $auth_provider_id
 * @property string|null $auth_provider
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $remember_token
 */

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
