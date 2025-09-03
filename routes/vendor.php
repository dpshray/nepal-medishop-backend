<?php

use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Controllers\Api\V1\Vendor\VendorAuthController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('vendor')
    ->group(function () {
        Route::middleware(['auth:sanctum'])->group(function(){

        });
        Route::post('registration', [VendorAuthController::class, 'registerVendor']);
    });
