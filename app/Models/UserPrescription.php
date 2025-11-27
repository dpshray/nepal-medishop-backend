<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserPrescription extends Model implements HasMedia
{
    //
    use InteractsWithMedia, HasEvents;
    const PRESCRIPTION_IMAGE = 'PRESCRIPTION_IMAGE';
    protected $fillable = [
        'user_id',
        'description',
    ];
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PRESCRIPTION_IMAGE)->singleFile();
    }
    function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
