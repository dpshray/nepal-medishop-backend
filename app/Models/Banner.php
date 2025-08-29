<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Banner extends Model implements HasMedia
{
    //
    use InteractsWithMedia, HasEvents;
    const MEDIA_NAME = 'banner';
    protected $fillable=[
        'title',
        'sub_title',
        'url',
    ];
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_NAME)
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('image')->nonQueued();
            });
    }
}
