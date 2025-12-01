<?php

namespace App\Models\Product\Service;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;

class ServiceTag extends Model
{
    use SlugTrait;
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];
}
