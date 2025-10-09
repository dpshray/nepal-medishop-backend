<?php

namespace App\Models;

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

    function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable')->where('reviewable_type', __CLASS__);
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PACKAGE_FEATURED)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
        $this->addMediaCollection(self::PACKAGE_GALLERY)->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
    public function products()
    {
        return $this->belongsToMany(ProductVariation::class, 'package_products')
            ->withPivot('quantity');
    }
}
