<?php

namespace App\Models\Product\Service;

use App\Models\Traits\SlugTrait;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use SlugTrait;
    public $timestamps = false;
    
    protected $fillable = [
        'is_active',
        'name',
        'description',
        'test_requirements',
        'price'
    ];
}
