<?php

use App\Http\Controllers\Api\V1\Client\LikeController;
use App\Http\Controllers\Api\V1\Client\MasterDataController;
use App\Http\Controllers\Api\V1\Client\Review\ProductReviewController;
use Illuminate\Support\Facades\Route;

Route::controller(MasterDataController::class)->group(function(){
    Route::get('get-brand-list', 'fetchAllActiveBrand');
    Route::get('get-category-list', 'fetchAllActiveCategory');
    Route::get('products', 'fetchProducts');
    Route::get('product/{product:slug}', 'fetchProductDetail');
    Route::get('packages', 'fetchPackages');
    Route::get('package/{package:slug}', 'fetchPackageDetail');
});
Route::middleware(['auth:sanctum'])->group(function() {
    Route::controller(LikeController::class)->group(function(){
        Route::get('favourite/{product:slug}/product', 'toggleProductFavourite');
        Route::get('favourite/{package:slug}/package', 'togglePackageFavourite');
    });
});
Route::apiResource('product.review',ProductReviewController::class)->except(['show'])->scoped(['product' => 'slug', 'review' => 'uuid']);
