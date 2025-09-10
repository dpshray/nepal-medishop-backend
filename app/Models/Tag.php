<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SlugTrait, SoftDeletes;

    public $timestamps = false;

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'name',
        'slug',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
