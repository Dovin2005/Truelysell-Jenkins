<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use App\Models\UserDetail;
use App\Models\Bookings;
use App\Models\ProviderSocialLink;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Product\app\Models\Rating;

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
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'user_type',
        'auth_provider_id',
        'auth_provider',
        'status',
        'user_language_id',
        'role_id',
        'sub_service_type',
        'provider_verified_status',
        'user_slug',
        'hourly_rate',
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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static string $userSecretKey = 'userId';


    /**
     * Get the category associated with the user.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }

    /**
     * Get the user details associated with the user.
     */
    public function userDetails(): HasOne
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    /**
     * Get the user who created this user.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the products created by the user.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(Bookings::class);
    }

    public function userDetail(): HasOne
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    public function getFullNameAttribute()
    {
        $fullName = ucwords(($this->UserDetail->first_name ?? '') . ' ' . ($this->UserDetail->last_name ?? ''));
        return $fullName ?? ucfirst($this->name ?? '');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->user_slug = Str::slug($user->name);
        });

        static::updating(function ($user) {
            // Update only if name has changed
            if ($user->isDirty('name')) {
                $user->user_slug = Str::slug($user->name);
            }
        });
    }

    public function providerSocialLinks()
    {
        return $this->hasMany(ProviderSocialLink::class, 'provider_id');
    }

    public function ratings() {
        return $this->hasMany(Rating::class, 'user_id');
    }

    public function documents()
    {
        return $this->hasMany(UserDocument::class, 'user_id');
    }

}
