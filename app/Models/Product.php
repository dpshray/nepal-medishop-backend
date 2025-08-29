<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    //
    use InteractsWithMedia, HasEvents;
    const MEDIA_NAME = 'product';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'pattern',
        'fabric',
        'material',
    ];
    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'product_categories');
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_NAME)
            // ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('image')->nonQueued();
            });
    }
    public function getDiscountPercentAttribute()
    {
        if ($this->discount_price && $this->price > 0) {
            return round((($this->price - $this->discount_price) / $this->price) * 100, 2);
        }
        return null;
    }
}
