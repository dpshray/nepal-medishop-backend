<?php

namespace App\Models;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class Kitbag extends Model
{
    use UuidModelTrait;

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'created_at'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime'
        ];
    }

    function user() {
        return $this->belongsTo(User::class);
    }

    function kitbagItems() {
        return $this->hasMany(KitbagItem::class);
    }
}
