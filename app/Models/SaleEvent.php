<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleEvent extends Model
{
    public $timestamps = false;
    
    function saleEventProducts(){
        return $this->hasMany(SaleEventProduct::class);
    }
}
