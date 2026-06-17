<?php

namespace App\Models\ProductVariantType;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class PackageType extends Model
{
    //
    use UuidModelTrait;
    protected $fillable = [
        'form_type_id',
        'name',
    ];
    protected $casts = [
        'form_type_id' => 'integer',
    ];
    public function formType()
    {
        return $this->belongsTo(FormType::class, 'form_type_id', 'id');
    }
    public function unitTypes()
    {
        return $this->hasMany(UnitType::class, 'package_type_id', 'id');
    }
}
