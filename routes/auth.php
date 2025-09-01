<?php

use App\Http\Controllers\Api\V1\Client\ClientAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->controller(ClientAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::get('verification/verify', 'emailVerifier')->name('verification.verify')->middleware(['signed']);

    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
    //forget password
    Route::post('/forget-password', 'forget_password');
    //reset password
    Route::post('/reset-password', 'reset_password');
});
