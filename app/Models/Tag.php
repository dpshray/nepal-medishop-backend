<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use SlugTrait;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    public function products(){
        return $this->belongsToMany(Product::class);
    }
}
