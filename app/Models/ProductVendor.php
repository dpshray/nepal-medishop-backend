<?php

namespace App\Models;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVendor extends Model
{
    use SoftDeletes, UuidModelTrait;
    
    protected $fillable = [
        'uuid',
        'product_id',
        'units_in_stock',
        'vendor_id',
        'is_approved'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    function vendorPrices()
    {
        return $this->hasMany(VendorProductPrice::class);
    }

    function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    function associatedVendor() {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    function scopeActive($qry) {
        $qry->where('status',1);
    }
}
