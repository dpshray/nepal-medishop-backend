<?php

namespace App\Models\Product\Service;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
class Service extends Model implements HasMedia
{
    use SlugTrait, InteractsWithMedia;

    const SERVICE_MEDIA = 'SERVICE_MEDIA';
    
    protected $fillable = [
        "is_active",
        "name",
        "description",
        "test_requirements",
        "price",
        "discount_percent"
    ];

    function categories() {
        return $this->belongsToMany(ServiceCategory::class,'category_service');
    }

    function tags() {
        return $this->belongsToMany(ServiceTag::class,'service_tag');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::SERVICE_MEDIA)->singleFile();
    }
}
