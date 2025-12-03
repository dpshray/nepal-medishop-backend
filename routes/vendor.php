<?php

use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Controllers\Api\V1\Vendor\Dashboard\VendorDashboardController;
use App\Http\Controllers\Api\V1\Vendor\Notification\OrderAssignNotificationController;
use App\Http\Controllers\Api\V1\Vendor\Order\VendorOrderAssignController;
use App\Http\Controllers\Api\V1\Vendor\Service\VendorServiceBookingController;
use App\Http\Controllers\Api\V1\Vendor\Service\VendorServiceController;
use App\Http\Controllers\Api\V1\Vendor\VendorAuthController;
use App\Http\Controllers\Api\V1\Vendor\VendorProductController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VendorMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('vendor')
    ->group(function () {
        Route::middleware(['auth:sanctum', VendorMiddleware::class])->group(function(){
            Route::controller(VendorProductController::class)->group(function(){
                Route::get('available-product', 'index');
                Route::get('product-list', 'vendorProductList');
                Route::get('product-detail/{product:uuid}', 'vendorProductDetail');
                Route::delete('product-delete/{product:uuid}', 'vendorProductRemover');
                Route::get('product-variants/{product:uuid}', 'productVariants');
                Route::post('product/{uuid?}', 'store');
            });
            Route::apiResource('orders',VendorOrderAssignController::class)->except(['destroy','store'])->scoped(['order' => 'uuid']);
            Route::get('get-variant-batch-numbers/{variant}', [VendorOrderAssignController::class, 'fetchVariantBatchNumbers']);
            Route::post('order-items/batch-assign/{order:uuid}', [VendorOrderAssignController::class, 'assignBatchesToOrderItems']);
            Route::apiResource('service', VendorServiceController::class)->except(['update'])->scoped(['service' => 'slug']);
            Route::get('registered-services', [VendorServiceController::class, 'getRegisteredServices']);
            Route::get('assigned-service-bookings', [VendorServiceBookingController::class, 'servicesAssignedToVendor']);
            Route::post('update-booking-status/{service_booking:uuid}', [VendorServiceBookingController::class, 'bookingStatusUpdate']);
            Route::get('dashboard',[VendorDashboardController::class,'index']);
        });
        Route::post('registration', [VendorAuthController::class, 'registerVendor']);
        Route::get('notifications',[OrderAssignNotificationController::class,'getNotification']);
        Route::post('notifications/{id}/seen',[OrderAssignNotificationController::class,'seennotification']);
    });
