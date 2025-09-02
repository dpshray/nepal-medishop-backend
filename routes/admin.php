<?php

use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function(){
    Route::apiResource('vendor', AdminVendorController::class);
});