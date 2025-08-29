<?php

use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Client\MainController;
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

//client
Route::controller(MainController::class)->group(function () {
    Route::get('/product', 'product');
    Route::get('/categories', 'categories');
    Route::get('/new-arrivals', 'new_arrivals');
    Route::get('/product-detail/{slug}', 'product_detail');
});
