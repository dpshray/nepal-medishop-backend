<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SlugTrait, SoftDeletes;

    public $timestamps = false;

    protected $hidden = ['deleted_at'];

    protected $fillable=[
        'name',
        'slug',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function tags(){
        return $this->hasMany(Tag::class);
    }
}
