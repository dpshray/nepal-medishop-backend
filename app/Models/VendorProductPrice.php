<?php

namespace App\Models;

use App\Enums\Purchase\OrderItemStatusEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItemProduct;
use App\Models\Purchase\OrderItemProductBatchNumber;
use Illuminate\Database\Eloquent\Model;

class VendorProductPrice extends Model
{
    protected $fillable = [
        "product_vendor_id",
        "product_variation_id",
        "price",
        "units_in_stock",
        "batch_number",
        "manufacture",
        "expiry_date"
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::updated(function ($vpp) {
            $vpp->ProductVendor->touch(); // updates updated_at timestamp
        });
    }

    function ProductVendor()
    {
        return $this->belongsTo(ProductVendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    function orderItemProductBatchNumber()
    {
        return $this->hasMany(OrderItemProductBatchNumber::class);
    }

    function orders() {
        return $this->belongsToMany(Order::class,'order_item_products','product_variation_id','order_id');
    }

    /**
     * Stock left of a variant of a vendor
     */
    function getStockLeftAttribute()
    {
        $used_stocks = (
                $this->orderItemProductBatchNumber->sum('quantity') - 
                $this->orderItemProductBatchNumber()->whereHas(
                    'orderItemProduct', 
                    fn($q) => $q->whereRelation('orderItem','status', OrderItemStatusEnum::CANCELLED)
                        ->orWhereRelation('order','status', OrderStatusEnum::CANCELLED)
                    )->sum('quantity')
            );
        return $this->units_in_stock - $used_stocks;
    }

    function scopeActive($qry)
    {
        return $qry->where('status', 1);
    }
}
