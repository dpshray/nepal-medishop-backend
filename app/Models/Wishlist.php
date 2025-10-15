<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id'
    ];

    function product()
    {
        return $this->belongsTo(Product::class, 'wishable_id');
    }

    function package()
    {
        return $this->belongsTo(Package::class, 'wishable_id');
    }
}
