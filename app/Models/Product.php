<?php

namespace App\Models;

use App\Models\Purchase\OrderItem;
use App\Models\Traits\SlugTrait;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    //
    use InteractsWithMedia, HasEvents, SoftDeletes, UuidModelTrait, SlugTrait;

    const PRODUCT_FEATURE = 'PRODUCT_FEATURE';
    const PRODUCT_GALLERY = 'PRODUCT_GALLERY';
    
    protected $fillable = [
        'added_by',
        'updated_by',
        'brand_id',
        'name',
        'slug',
        'description',
        'status',
        'rating',
        'prescription_required'
    ];
    
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    function brand(){
        return $this->belongsTo(Brand::class);
    }

    function healthConditions() {
        return $this->belongsToMany(HealthCondition::class);
    }

    public function cheapestVariation()
    {
        return $this->hasOne(ProductVariation::class)->orderBy('platform_price');
    }

    function productVendors(){
        return $this->hasMany(ProductVendor::class);
    }

    function productVendorPrices(){
        return $this->hasManyThrough(VendorProductPrice::class, ProductVendor::class);
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

    function scopeActive($query){
        return $query->where('status', 1);
    }

    function orderItem() {
        return $this->hasMany(OrderItem::class,'item_id')->where('item_type', Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PRODUCT_FEATURE)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
        $this->addMediaCollection(self::PRODUCT_GALLERY)->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
