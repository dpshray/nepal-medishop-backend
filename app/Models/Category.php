<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    use SlugTrait, SoftDeletes, InteractsWithMedia;

    public $timestamps = false;

    const CATEGORY_IMAGE = 'CATEGORY_IMAGE';

    protected $hidden = ['deleted_at'];

    protected $fillable=[
        'name',
        'slug',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function tags(){
        return $this->hasMany(Tag::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::CATEGORY_IMAGE)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
