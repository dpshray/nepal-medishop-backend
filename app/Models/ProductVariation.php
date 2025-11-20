<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date'
        ];
    }

    protected $fillable = [
        'name',
        'size_value',
        'size_unit',
        'platform_price',
        'platform_discount_price',
        'batch_number',
        'manufacture',
        'expiry_date'
    ];

    function product()
    {
        return $this->belongsTo(Product::class);
    }

    function getOriginalPriceAttribute(): array
    {
        $product = $this->product;
        $price = $this->platform_price;
        $previous_price = null;
        $discount_percent = null;
        if ($product->discount_percent > 0) { #first product discount
            $discount_percent = $product->discount_percent;
            $previous_price = $price;
            $price =  ($price - (($product->discount_percent * $price) / 100));
        } elseif ($product->categories->firstWhere('discount_percent', '>', 0)) { #second category discount
            $discount_percent = $product->categories->firstWhere('discount_percent', '>', 0)->discount_percent;
            $previous_price = $price;
            $price = ($price - (($discount_percent * $price) / 100));
        }
        $previous_price = empty($previous_price) ? $previous_price : (float) round($previous_price, 2);
        return ['price' => (float) round($price, 2), 'previous_price' => $previous_price, 'discount_percent' => (float) $discount_percent];
    }

    function vendorProductPrices()
    {
        return $this->hasMany(VendorProductPrice::class, 'product_variation_id');
    }
    function vendorProductPrice()
    {
        return $this->hasOne(VendorProductPrice::class, 'product_variation_id');
    }
}
