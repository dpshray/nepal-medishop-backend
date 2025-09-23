<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
class Brand extends Model implements HasMedia
{
    use SlugTrait, InteractsWithMedia;

    const BRAND_IMAGE = 'BRAND_IMAGE';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'is_featured',
        'is_popular',
        'status'
    ];

    public function scopeActive($qry){
        return $qry->where('status',1);
    }

    function products() {
        return $this->hasMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::BRAND_IMAGE)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
