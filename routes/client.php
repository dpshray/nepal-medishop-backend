<?php

use App\Enums\ClientProductSectionEnum;
use App\Http\Controllers\Api\V1\Client\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::controller(MasterDataController::class)->group(function(){
    Route::get('get-brand-list', 'fetchAllActiveBrand');
    Route::get('get-category-list', 'fetchAllActiveCategory');
    Route::get('fetch-section/{section}', 'fetchProductSection')->whereIn('section', ClientProductSectionEnum::cases());
});