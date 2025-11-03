<?php

namespace App\Models;

use App\Models\Traits\SlugTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HealthCondition extends Model implements HasMedia
{
    use SlugTrait, InteractsWithMedia;
    
    protected $fillable = [
        'name',
        'description'
    ];
    
    public $timestamps = false;

    const HEALTH_CONDITION_IMAGE = 'HEALTH_CONDITION_IMAGE';

    function scopeActive($qry) {
        return $qry->where('status',true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::HEALTH_CONDITION_IMAGE)->singleFile()->useFallbackUrl(asset('assets/img/default-brand-category.png'));
    }
}
