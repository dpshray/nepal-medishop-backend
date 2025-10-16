<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserTypeEnum;
use App\Models\Purchase\Cart;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, UuidModelTrait, SoftDeletes, InteractsWithMedia;
    const USER_PROFILE = 'USER_PROFILE';
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'user_type',
        'mobile_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function scopeFilterByRole($query, UserTypeEnum $role)
    {
        return $query->where('user_type', (string)$role->value);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    function vendorProducts()
    {
        return $this->hasMany(ProductVendor::class, 'vendor_id');
    }

    function isAdmin()
    {
        return $this->user_type == UserTypeEnum::ADMIN->value;
    }

    function isVendor()
    {
        return $this->user_type == UserTypeEnum::VENDOR->value;
    }

    function likes()
    {
        return $this->morphMany(Like::class, 'likable');
    }

    function cart()
    {
        return $this->hasMany(Cart::class);
    }

    function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latest();
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::USER_PROFILE)
            ->singleFile()
            ->useFallbackUrl(asset('assets/img/user-profile-default.png'))
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('image')->nonQueued();
            });
    }
}
