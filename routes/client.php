<?php

use App\Enums\ItemTypeEnum;
use App\Http\Controllers\Api\V1\Client\LikeController;
use App\Http\Controllers\Api\V1\Client\MasterDataController;
use App\Http\Controllers\Api\V1\Client\Profile\ClientProfileController;
use App\Http\Controllers\Api\V1\Client\Purchase\ClientCartController;
use App\Http\Controllers\Api\V1\Client\Purchase\CODPurchaseController;
use App\Http\Controllers\Api\V1\Client\Review\PackageReviewController;
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
    Route::controller(ClientCartController::class)->group(function(){
        Route::post('add-to-cart', 'storeOnCart');
        Route::get('my-cart', 'fetchMyCart');
        Route::get('remove-cart-item/{cart:uuid}', 'cartItemRemover');
        Route::post('update-cart-item/{cart:uuid}', 'cartItemUpdater');
    });
});
Route::apiResource('product.review', ProductReviewController::class)->except(['show'])->scoped(['product' => 'slug', 'review' => 'uuid']);
Route::get('fetch-product-ratings/{product:slug}', [ProductReviewController::class, 'getProductRatingsByAllUser']);
Route::apiResource('package.review', PackageReviewController::class)->except(['show'])->scoped(['package' => 'slug', 'review' => 'uuid']);
Route::get('fetch-package-ratings/{package:slug}', [PackageReviewController::class, 'getPackageRatingsByAllUser']);
// Route::apiResource('profile', ClientProfileController::class)->except(['show']);
Route::get('user/profile', [ClientProfileController::class, 'index']);
Route::put('user/profile', [ClientProfileController::class, 'update']);



/*=====  Purchase Part  ======*/
Route::post('orders', CODPurchaseController::class);
