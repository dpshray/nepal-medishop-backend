<?php

use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function(){
    $rows = Order::find(8)->orderItems()->with(['product.variations', 'package'])->get();
    dd($rows);
});