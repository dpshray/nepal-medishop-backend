<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
class Brand extends Model implements HasMedia
{
    use SlugTrait, SoftDeletes, InteractsWithMedia;

    const BRAND_IMAGE = 'BRAND_IMAGE';

    public $timestamps = false;

    protected $hidden = ['deleted_at'];

    protected $fillable = [
        'name',
        'is_featured',
        'is_popular',
        'status'
    ];

    public function scopeActive($qry){
        return $qry->where('status',1);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::BRAND_IMAGE)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
