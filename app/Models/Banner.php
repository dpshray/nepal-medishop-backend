<?php

namespace App\Models;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    use InteractsWithMedia, HasEvents, UuidModelTrait;

    const BANNER_MEDIA = 'BANNER_MEDIA';
    
    public $timestamps = false;
    protected $fillable=[
        'display_status',
        'order',
        'title',
        'url',
        'description'
    ];
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::BANNER_MEDIA)
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('image')->nonQueued();
            });
    }

    function scopeVisible($query) {
        return $query->where('display_status',1);
    }
}
