<?php

namespace App\Models;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;


class Disclaimer extends Model
{
    use UuidModelTrait;
    protected $fillable = [
        'uuid',
        'disclaimer',
    ];
}
