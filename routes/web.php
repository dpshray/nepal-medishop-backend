<?php

use App\Models\Product;
use App\Models\ProductVendor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function(){
    $result = ProductVendor::withSum('product','vendorPrices', 'units_in_stock')->first();
    dd($result);
});