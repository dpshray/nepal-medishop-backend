<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorNotification extends Model
{
    //
    protected $fillable=[
        'vendor_id',
        'title',
        'body',
        'is_seen'
    ];
}
