<?php

namespace App\Models\Purchase;

use App\Models\Package;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OrderItem extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public $timestamps = false;

    const PRESCRIPTION_IMAGE = 'PRESCRIPTION_IMAGE';

    protected $fillable = [
        'item_type',
        'item_id',
        'item_name',
        'item_slug',
        'item_variant_id',
        'variant_name',
        'variant_size',
        'quantity',
        'price',
        'image',
        'total',
        'created_at'
    ];

    function product(){
        return $this->belongsTo(Product::class,'item_id')->whereHas('orderItem', function ($q) {
            $q->where('item_type', Product::class);
        });
    }

    function productVariant() {
        return $this->belongsTo(ProductVariation::class,'item_variant_id');
    }

    function package() {
        return $this->belongsTo(Package::class,'item_id')->whereHas('orderItem', function ($q) {
            $q->where('item_type', Package::class);
        });
    }

    public function item()
    {
        return $this->morphTo();
    }

    function assignedVendor()
    {
        return $this->belongsTo(Vendor::class, 'assigned_vendor_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PRESCRIPTION_IMAGE)->singleFile();
    }
}
