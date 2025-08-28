<?php

use App\Http\Controllers\V1\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
    // email verification
    Route::get('/email/verify/{id}/{hash}', 'Email_verify')
        ->middleware(['auth', 'signed'])
        ->name('verification.verify');
    //forget password
    Route::post('/forget-password', 'forget_password');
    //reset password
    Route::post('/reset-password', 'reset_password');
});
