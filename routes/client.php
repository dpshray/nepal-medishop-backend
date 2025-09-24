<?php

use App\Http\Controllers\Api\V1\Client\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::controller(MasterDataController::class)->group(function(){
    Route::get('get-brand-list', 'fetchAllActiveBrand');
    Route::get('get-category-list', 'fetchAllActiveCategory');
    Route::get('products', 'fetchProducts');
    Route::get('product/{product:slug}', 'fetchProductDetail');
    Route::get('packages', 'fetchPackages');
    Route::get('package/{package:slug}', 'fetchPackageDetail');
});