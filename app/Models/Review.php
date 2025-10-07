<?php

namespace App\Models;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use UuidModelTrait;

    protected $fillable = [
        'review',
        'user_id',
        'uuid',
        'rating',
        'created_at',
        'updated_at'
    ];

    function user() {
        return $this->belongsTo(User::class);
    }
}
