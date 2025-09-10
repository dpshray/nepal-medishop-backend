<?php

use App\Http\Controllers\Api\V1\Client\ClientAuthController;
use Illuminate\Support\Facades\Route;

Route::controller(ClientAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::middleware(['auth:sanctum'])->post('/logout', 'logout');
    Route::post('/register', 'register');
    Route::get('verification/verify', 'emailVerifier')->name('verification.verify')->middleware(['signed']);
    Route::post('forgot-password', 'sendPasswordResetLink');
    Route::match(['GET', 'POST'], 'password-resetor/{token}', 'paswordResetorFormHandler')->name('password.reset');
});
