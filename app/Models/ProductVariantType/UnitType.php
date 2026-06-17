<?php

namespace App\Models\ProductVariantType;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    //
    use UuidModelTrait;
    protected $fillable = [
        'package_type_id',
        'name',
    ];
    protected $casts = [
        'package_type_id' => 'integer',
    ];
    public function packageType()
    {
        return $this->belongsTo(PackageType::class, 'package_type_id', 'id');
    }
}
