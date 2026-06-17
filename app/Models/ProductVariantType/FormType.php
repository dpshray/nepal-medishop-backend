<?php

namespace App\Models\ProductVariantType;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class FormType extends Model
{
    //
    use UuidModelTrait;
    protected $fillable = [
        'name',
    ];
    public function packageTypes()
    {
        return $this->hasMany(PackageType::class);
    }
}
