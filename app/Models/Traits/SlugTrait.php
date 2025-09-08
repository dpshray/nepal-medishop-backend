<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait SlugTrait
{
    public static function bootSlugTrait()
    {
        static::creating(function ($model) {
            $table_name = $model->getTable();
            $default_slug = Str::slug($model->name);
            $count = DB::table($table_name)->where('slug','like', "$default_slug%")->count();          
            $model->slug = $count ? $default_slug."-$count" : $default_slug;
        });
        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $table_name = $model->getTable();
                $base_slug = Str::slug($model->name);
                $count = DB::table($table_name)
                    ->where('slug', 'like', "$base_slug%")
                    ->where('id', '!=', $model->id)
                    ->count();
                $model->slug = $count ? "{$base_slug}-{$count}" : $base_slug;
            }
        });
    }
}
