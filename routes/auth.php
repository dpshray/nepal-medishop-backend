<?php

use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Client\ClientAuthController;
use App\Http\Controllers\Api\V1\Vendor\VendorAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->controller(ClientAuthController::class)->group(function () {
    Route::controller(ClientAuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
        Route::post('/register', 'register');
        Route::get('verification/verify', 'emailVerifier')->name('verification.verify')->middleware(['signed']);
        // Route::post('/forget-password', 'forget_password');        //reset password
        // Route::post('/reset-password', 'reset_password');
    });
    Route::prefix('admin')->controller(AdminAuthController::class)->group(function(){
        Route::post('login','login');
    });
    Route::prefix('vendor')->controller(VendorAuthController::class)->group(function(){
        Route::post('login','login');
    });
});
