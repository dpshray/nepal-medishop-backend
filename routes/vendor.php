<?php

use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Controllers\Api\V1\Vendor\VendorAuthController;
use App\Http\Controllers\Api\V1\Vendor\VendorProductController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VendorMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('vendor')
    ->group(function () {
        Route::middleware(['auth:sanctum', VendorMiddleware::class])->group(function(){
            Route::controller(VendorProductController::class)->group(function(){
                Route::get('product', 'index');
                Route::get('product-variants/{product:uuid}', 'productVariants');
                Route::post('product/{uuid?}', 'store');
            });
        });
        Route::post('registration', [VendorAuthController::class, 'registerVendor']);
    });
