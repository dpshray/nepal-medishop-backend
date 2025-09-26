<?php

use App\Enums\AdminUrlParamEnum;
use App\Enums\RouteParamEnum;
use App\Http\Controllers\Api\V1\Admin\AdminSharedController;
use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminBrandController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminTagController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function(){
        Route::apiResource('vendor', AdminVendorController::class)->parameters(['vendor' => 'user'])->scoped(['user' => 'uuid']);
        Route::get('fetch-vendor-products/{user:uuid}', [AdminVendorController::class, 'getVendorProduct']);
        Route::controller(AdminVendorController::class)->group(function(){
            Route::get('vendor-verified-toggler/{user:uuid}', 'toggleVendorVerifiedStatus');
        });
        Route::apiResource('brand', AdminBrandController::class);
        Route::apiResource('category', AdminCategoryController::class);
        Route::apiResource('tag', AdminTagController::class);
        Route::get('toggle-brand-status/{brand:slug}', [AdminBrandController::class, 'statusToggler']);
        Route::get('toggle-category-status/{category:slug}', [AdminCategoryController::class, 'statusToggler']);
        Route::get('toggle-tag-status/{tag:slug}', [AdminTagController::class, 'statusToggler']);
        Route::apiResource('product', AdminProductController::class)->scoped(['product' => 'uuid']);
        Route::get('toggle-product-status/{product:uuid}', [AdminProductController::class, 'statusToggler']);
        Route::post('product-media/{product:uuid}', [AdminProductController::class, 'storeMedia']);
        Route::get('product-units', [AdminProductController::class, 'productUnits']);
});