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
    require __DIR__ . '/auth.php';
    require __DIR__ . '/admin.php';
});
