<?php

namespace App\Models\Product\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    protected $fillable = [
        'status',
        'appointment_at',
        'service_id',
        'message'
    ];

    protected $casts = [
        'status' => ServiceBookingStatusEnum::class
    ];
}
