<?php

namespace App\Models\Product\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Models\Traits\UuidModelTrait;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ServiceBooking extends Model implements HasMedia
{
    use UuidModelTrait, InteractsWithMedia;

    const SERVICE_BOOKING_REPORT = 'SERVICE_BOOKING_REPORT';
    
    protected $fillable = [
        'status',
        'assigned_vendor_id',
        'appointment_at',
        'service_id',
        'message'
    ];

    protected $casts = [
        'status' => ServiceBookingStatusEnum::class,
        'appointment_at' => 'datetime'
    ];
    
    function orderedBy() {
        return $this->belongsTo(User::class,'user_id');
    }

    function assignedVendor() {
        return $this->belongsTo(Vendor::class, 'assigned_vendor_id');
    }

    function service() {
        return $this->belongsTo(Service::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::SERVICE_BOOKING_REPORT)->singleFile();
    }
}
