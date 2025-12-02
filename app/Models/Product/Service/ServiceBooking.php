<?php

namespace App\Models\Product\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use UuidModelTrait;
    
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
