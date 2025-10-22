<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id'
    ];

    function product() {
        return $this->belongsTo(Product::class, 'likable_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function likable()
    {
        return $this->morphTo();
    }
}
