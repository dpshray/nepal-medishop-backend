<?php

namespace App\Models;

use App\Constants\VendorContants;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\{HasMedia, InteractsWithMedia, MediaCollections\Models\Media};

class Vendor extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, UuidModelTrait;
    
    protected $dates = ['deleted_at'];

    protected $fillable = [
        "store_name",
        "store_description",
        "location",
        "country",
        "state",
        "district",
        "municipality",
        "postal_code",
        "bank_name",
        "bank_account_holder_name",
        "bank_account_number",
        "is_verified"
    ];

    const CITIZENSHIP_CARD = 'VENDOR_CITIZENSHIP_CARD';
    const BUSINESS_LICENSE = 'VENDOR_BUSINESS_LICENSE';
    const TAX_CERTIFICATE = 'VENDOR_TAX_CERTIFICATE';

    function user(){
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
        $this->addMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
        $this->addMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);
    }
}
