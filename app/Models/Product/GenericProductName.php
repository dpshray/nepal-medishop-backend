<?php

namespace App\Models\Product;

use App\Models\Traits\SlugTrait;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenericProductName extends Model
{
    use SlugTrait, SoftDeletes;
    public $timestamps = false;
    
    protected $fillable = [
        'status',
        'slug',
        'name'
    ];
}
