<?php

namespace App\Models\Payment;

use App\Models\Purchase\Order;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use UuidModelTrait;

    protected $fillable = [
        'uuid',
        'order_id',
        'payment_gateway',
        'payment_status',
        'transaction_id',
        'reference_id',
        'amount',
        'currency',
        'paid_at',
        'failed_at',
        'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
