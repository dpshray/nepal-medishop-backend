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
        Route::post('forgot-password', 'sendPasswordResetLink');
        Route::match(['GET', 'POST'], 'password-resetor/{token}', 'paswordResetorFormHandler')->name('password.reset');
    });
});
