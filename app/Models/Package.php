<?php

namespace App\Models;

use App\Models\Purchase\OrderItem;
use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;

class Package extends Model implements HasMedia
{
    use InteractsWithMedia, SlugTrait;

    const PACKAGE_FEATURED = 'PACKAGE_FEATURED';
    const PACKAGE_GALLERY = 'PACKAGE_GALLERY';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_percent',
        'start_timestamps',
        'end_timestamps',
        'rating',
        'status',
    ];

    public $timestamps = false;

    function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    function packageProducts()
    {
        return $this->hasMany(PackageProduct::class);
    }

    function likes()
    {
        return $this->morphMany(Like::class, 'likable')->where('likable_type', __CLASS__);
    }

    function wishlists()
    {
        return $this->morphMany(Wishlist::class, 'wishable')->where('wishable_type', __CLASS__);
    }

    function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable')->where('reviewable_type', __CLASS__);
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PACKAGE_FEATURED)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
        $this->addMediaCollection(self::PACKAGE_GALLERY)->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }

    function orderItem()
    {
        return $this->hasMany(OrderItem::class, 'item_id')->where('item_type', Package::class);
    }

    function getOriginalPriceAttribute(): array
    {
        $price = $this->price;
        $previous_price = null;
        $discount_percent = null;
        
        // Apply discount only if discount_percent exists and is greater than 0
        if (!is_null($this->discount_percent) && $this->discount_percent > 0) {
            $discount_percent = $this->discount_percent;
            $previous_price = (float) round($price, 2);
            $price = ($price - (($this->discount_percent * $price) / 100));
        }

        return [
            'price' => (float) round($price, 2),
            'previous_price' => $previous_price,
            'discount_percent' => (float) $discount_percent
        ];
    }


    public function products()
    {
        return $this->belongsToMany(ProductVariation::class, 'package_products')
            ->withPivot('quantity');
    }
}
