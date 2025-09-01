<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Variant extends Model implements HasMedia
{
    //
    use InteractsWithMedia, HasEvents;
    use SoftDeletes;
    const MEDIA_NAME = 'variant';
    protected $fillable = [
    'product_id',
    'size',
    'color',
    'price',
    'discount_price',
    'stock',
];
    public function product()
    {
        return $this->belongsTo(Product::class);
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
