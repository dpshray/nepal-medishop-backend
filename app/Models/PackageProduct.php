<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageProduct extends Model
{
    public $timestamps = false;

    public function variant(){
        return $this->belongsTo(ProductVariation::class,'product_variation_id');
    }
}
