<?php

namespace App\Models\Point;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class CouponCode extends Model
{
    //
    use UuidModelTrait;
    protected $fillable = [
        'code',
        'discount_percent',
        'start_date',
        'end_date',
        'is_active',
        'description'
    ];
}
