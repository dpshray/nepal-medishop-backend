<?php

use App\Enums\ItemTypeEnum;
use App\Http\Controllers\Api\V1\Client\Address\ClientAddressController;
use App\Http\Controllers\Api\V1\Client\ClientBannerController;
use App\Http\Controllers\Api\V1\Client\ClientKitbagController;
use App\Http\Controllers\Api\V1\Client\Service\ClientServiceController;
use App\Http\Controllers\Api\V1\Client\Feedback\ClientFeedbackController;
use App\Http\Controllers\Api\V1\Client\LikeController;
use App\Http\Controllers\Api\V1\Client\MasterDataController;
use App\Http\Controllers\Api\V1\Client\OAuthController;
use App\Http\Controllers\Api\V1\Client\Password\ClientPasswordController;
use App\Http\Controllers\Api\V1\Client\Prescription\ClientPrescriptionController;
use App\Http\Controllers\Api\V1\Client\Profile\ClientProfileController;
use App\Http\Controllers\Api\V1\Client\Promocode\ClientPromoCodeController;
use App\Http\Controllers\Api\V1\Client\Purchase\ClientCartController;
use App\Http\Controllers\Api\V1\Client\Purchase\ClientOrderController;
use App\Http\Controllers\Api\V1\Client\Purchase\CODPurchaseController;
use App\Http\Controllers\Api\V1\Client\Review\ClientGrievanceController;
use App\Http\Controllers\Api\V1\Client\Review\PackageReviewController;
use App\Http\Controllers\Api\V1\Client\Review\ProductReviewController;
use App\Http\Controllers\Api\V1\Client\Service\ClientServiceBookingController;
use App\Http\Controllers\Api\V1\Client\WishlistController;
use Illuminate\Support\Facades\Route;

Route::controller(MasterDataController::class)->group(function(){
    Route::get('get-brand-list', 'fetchAllActiveBrand');
    Route::get('get-category-list', 'fetchAllActiveCategory');
    Route::get('get-health-condition-list', 'fetchAllHealthCondition');
    Route::get('products', 'fetchProducts');
    Route::get('product/{product:slug}', 'fetchProductDetail');
    Route::get('packages', 'fetchPackages');
    Route::get('package/{package:slug}', 'fetchPackageDetail');
    Route::get('settings', 'fetchSettings');
});
Route::middleware(['auth:sanctum'])->group(function() {
    Route::controller(LikeController::class)->group(function(){
        Route::get('favourite/{product:slug}/product', 'toggleProductFavourite');
        Route::get('liked-items', 'myLikedItems');
    });
    Route::controller(WishlistController::class)->group(function(){
        Route::get('wishlist/{product:slug}/product', 'toggleProductWishlist');
        Route::get('wishlist/{package:slug}/package', 'togglePackageWishlist');
        Route::get('wishlist-items', 'myWishlist');

    });
    Route::controller(ClientCartController::class)->group(function(){
        Route::post('add-to-cart', 'storeOnCart');
        Route::get('my-cart', 'fetchMyCart');
        Route::delete('remove-cart-item', 'cartItemRemover');
        Route::post('update-cart-item/{cart:uuid}', 'cartItemUpdater');
    });
    Route::apiResource('kitbag', ClientKitbagController::class)->except(['show','update','destroy'])->scoped(['kitbag' => 'uuid']);
    Route::delete('kitbag', [ClientKitbagController::class, 'destroy']);
    
    Route::post('user/grievance', [ClientGrievanceController::class, 'store']);
    Route::get('user/grievance', [ClientGrievanceController::class, 'index']);
    Route::get('user/grievance/{grievance:uuid}', [ClientGrievanceController::class, 'show']);
    
    Route::apiResource('user/address',ClientAddressController::class)->except(['show']);
    Route::get('user/profile', [ClientProfileController::class, 'index']);
    Route::put('user/profile', [ClientProfileController::class, 'update']);
    Route::post('user/change-password',[ClientPasswordController::class,'ChangePassword']);
});
Route::apiResource('product.review', ProductReviewController::class)->except(['show'])->scoped(['product' => 'slug', 'review' => 'uuid']);
Route::get('fetch-product-ratings/{product:slug}', [ProductReviewController::class, 'getProductRatingsByAllUser']);
Route::apiResource('package.review', PackageReviewController::class)->except(['show'])->scoped(['package' => 'slug', 'review' => 'uuid']);
Route::get('fetch-package-ratings/{package:slug}', [PackageReviewController::class, 'getPackageRatingsByAllUser']);
// Route::apiResource('profile', ClientProfileController::class)->except(['show']);
Route::get('banner', ClientBannerController::class);

//feedback for client
Route::apiResource('user/feedback',ClientFeedbackController::class)->except(['show','update','destroy','index']);

/*=====  Services and Booking  ======*/
Route::controller(ClientServiceController::class)->group(function(){
    Route::get('get-services', 'index');
    Route::get('get-services/{service:slug}', 'show');
});
Route::middleware(['auth:sanctum'])->group(function(){
    Route::controller(ClientServiceBookingController::class)->group(function(){
        Route::post('book-service/{service:slug}', 'serviceBooking');
        Route::get('fetch-service-booking-history', 'index');
        Route::get('fetch-service-booking-detail/{service_booking:uuid}', 'show');
    });
});
/*=====  Purchase Part  ======*/
Route::controller(CODPurchaseController::class)->group(function(){
    Route::post('orders', 'regularOrder');
    Route::post('kitbag-orders', 'kitbagOrder');
});
Route::middleware(['auth:sanctum'])->controller(ClientOrderController::class)->group(function(){
    Route::get('my-orders', 'index');
    Route::get('my-order-detail/{order:uuid}', 'orderDetail');
});
Route::post('check-coupon',[ClientPromoCodeController::class,'checkcode']);
Route::middleware(['auth:sanctum'])->group(function ()
{
    Route::apiResource('user/prescription',ClientPrescriptionController::class)->only(['index','store','destroy']);
});
/*=====  OAUTH Login  ======*/
Route::post('login/google', OAuthController::class);
