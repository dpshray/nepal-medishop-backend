<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public $timestamps = false;

    function packageProducts(){
        return $this->hasMany(PackageProduct::class);
    }
}
