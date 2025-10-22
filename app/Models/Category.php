<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    use SlugTrait, InteractsWithMedia;

    public $timestamps = false;

    const CATEGORY_IMAGE = 'CATEGORY_IMAGE';

    protected $fillable=[
        'name',
        'slug',
        'status',
        'discount_percent'
    ];

    public function scopeActive($qry)
    {
        return $qry->where('status', 1);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::CATEGORY_IMAGE)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
