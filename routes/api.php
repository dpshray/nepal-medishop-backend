<?php

use App\Http\Controllers\V1\Admin\BannerController;
use App\Http\Controllers\V1\Admin\CategoriesController;
use App\Http\Controllers\V1\Admin\ProductController;
use App\Http\Controllers\V1\Admin\UserController;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Client\MainController;
use App\Http\Middleware\AdminMiddleware;
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
    Route::get('/product-detail/{slug}', 'product_detail');
    Route::get('/banner','banner');
});

//Admin section
Route::prefix('admin')->group(function () {
    Route::middleware(['auth:sanctum', 'verified', AdminMiddleware::class])->group(function () {
        Route::controller(ProductController::class)->group(function () {
            Route::post('/add-product', 'add_product');
            Route::post('/update-product/{product}', 'update_product');
            Route::delete('/delete-product/{product}', 'delete_product');
        });
        Route::controller(BannerController::class)->group(function () {
            Route::post('/add-banner', 'add_banner');
            Route::post('/update-banner/{banner}', 'update_banner');
            Route::delete('/delete-banner/{banner}', 'delete_banner');
        });
        Route::controller(CategoriesController::class)->group(function () {
            Route::post('/add-categories', 'add_categories');
            Route::post('/update-categories/{categories}', 'update_categories');
            Route::delete('/delete-categories/{category}', 'delete_categories');
        });
        Route::controller(UserController::class)->group(function () {
            Route::get('/view-user', 'View_User');
            Route::delete('/delete-user/{user}', 'delete');
        });
    });
});
