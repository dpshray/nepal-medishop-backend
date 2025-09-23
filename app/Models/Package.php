<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;

class Package extends Model implements HasMedia
{
    use InteractsWithMedia;

    const PACKAGE_MEIDA = 'PACKAGE_MEIDA';

    public $timestamps = false;

    function packageProducts(){
        return $this->hasMany(PackageProduct::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PACKAGE_MEIDA)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
