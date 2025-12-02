<?php

namespace App\Models;

use App\Constants\VendorContants;
use App\Models\Product\Service\Service;
use App\Models\Purchase\OrderItem;
use App\Models\Purchase\OrderItemProduct;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia, MediaCollections\Models\Media};

class Vendor extends Model implements HasMedia
{
    use InteractsWithMedia, UuidModelTrait;
    
    protected $fillable = [
        "store_name",
        "store_description",
        "location",
        "country",
        "state",
        "district",
        "municipality",
        "postal_code",
        "bank_name",
        "bank_account_holder_name",
        "bank_account_number",
        "verified_at"
    ];

    const CITIZENSHIP_CARD = 'VENDOR_CITIZENSHIP_CARD';
    const BUSINESS_LICENSE = 'VENDOR_BUSINESS_LICENSE';
    const TAX_CERTIFICATE = 'VENDOR_TAX_CERTIFICATE';

    function user(){
        return $this->belongsTo(User::class);
    }

    function vendorProducts(){
        return $this->hasMany(ProductVendor::class);
    }

    function vendorProductPrices() {
        return $this->hasManyThrough(VendorProductPrice::class, ProductVendor::class);
    }

    function scopeVerifiedAndActive($query) {
        return $query->whereRelation('user','email_verified_at','<>',null)->whereRelation('user','status',1);
    }

    function assignedOrders() {
        return $this->hasMany(OrderItem::class,'assigned_vendor_id');
    }

    function orderItemProducts()
    {
        return $this->hasManyThrough(OrderItemProduct::class,OrderItem::class,'assigned_vendor_id');
    }

    function services() {
        return $this->belongsToMany(Service::class)->withPivot(['price', 'is_available']);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD)->singleFile();
        $this->addMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE)->singleFile();
        $this->addMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE)->singleFile();
    }
}
