<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    public $timestamps = false;
    
    function flashProducts(){
        return $this->hasMany(FlashSaleProduct::class);
    }
}
