<?php

use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function(){
        Route::apiResource('vendor', AdminVendorController::class);
});