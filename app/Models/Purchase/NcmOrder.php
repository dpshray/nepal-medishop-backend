<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UuidModelTrait;

class NcmOrder extends Model
{
    //
    use UuidModelTrait;
    protected $fillable = [
        'ncm_order_id',
        'order_id',
        'fbranch',
        'tbranch',
        'package',
        'weight',
        'cod_charge',
        'instruction',
        'delivery_type',
        'delivery_status',
        'delivery_charge',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
