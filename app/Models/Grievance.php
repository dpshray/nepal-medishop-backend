<?php

namespace App\Models;

use App\Enums\GrievanceEnum;
use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Grievance extends Model implements HasMedia
{
    use InteractsWithMedia, UuidModelTrait;

    const GRIEVANCE_IMAGE = 'GRIEVANCE_IMAGE';
    protected $fillable = [
        'status',
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'detail',
        'image'
    ];

    protected function casts(): array
    {
        return [
            'status' => GrievanceEnum::class
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::GRIEVANCE_IMAGE)
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('image')->nonQueued();
            });
    }
}
