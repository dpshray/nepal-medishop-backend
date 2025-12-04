<?php

namespace App\Models\Product\Service;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;

class ServiceVendor extends Model
{
    public $table = 'service_vendor';

    function service() {
        return $this->belongsTo(Service::class);
    }

    function vendor() {
        return $this->belongsTo(Vendor::class);
    }
}
