<?php

use App\Http\Controllers\V1\Admin\BannerController;
use App\Http\Controllers\V1\Admin\CategoriesController;
use App\Http\Controllers\V1\Admin\ProductController;
use App\Http\Controllers\V1\Admin\UserController;
use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Client\CartController;
use App\Http\Controllers\V1\Client\MainController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('v1')->group(function(){
    //client
    require __DIR__ . '/auth.php';
    require __DIR__ . '/admin.php';
    Route::controller(MainController::class)->group(function () {
        Route::get('/product', 'product');
        Route::get('/categories', 'categories');
        Route::get('/product-detail/{slug}', 'product_detail');
        Route::get('/banner', 'banner');
    });

    Route::controller(CartController::class)->group(function () {
        Route::get('/view-cart', 'view_cart')->middleware('auth:sanctum');
        Route::post('/add-to-cart', 'add_cart')->middleware('auth:sanctum');
        Route::post('/update-cart/{cart}', 'update_cart')->middleware('auth:sanctum');
        Route::delete('/delete-cart/{cart}', 'delete_from_cart')->middleware('auth:sanctum');
    });

    //Admin section
    Route::prefix('admin')->group(function () {
        Route::middleware(['auth:sanctum', 'verified', AdminMiddleware::class])->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::post('/add-product', 'add_product');
                Route::post('/update-product/{product}', 'update_product');
                Route::delete('/delete-product/{product}', 'delete_product');
                Route::post('/restore-product/{id}', 'restore_product');
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
});
