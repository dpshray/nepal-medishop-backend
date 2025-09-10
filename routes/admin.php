<?php

use App\Enums\RouteParamEnum;
use App\Http\Controllers\Api\V1\Admin\AdminSharedController;
use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminBrandController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminTagController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function(){
        Route::apiResource('vendor', AdminVendorController::class);
        Route::controller(AdminVendorController::class)->group(function(){
            Route::get('vendor-verified-toggler/{vendor:uuid}', 'toggleVendorVerifiedStatus');
        });
        Route::apiResource('brand', AdminBrandController::class);
        Route::apiResource('category', AdminCategoryController::class);
        Route::apiResource('category.tag', AdminTagController::class)->shallow()->scoped(['category' => 'slug']);
});