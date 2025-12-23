<?php

use App\Models\Product;
use App\Models\ProductVendor;
use App\Models\Purchase\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function(){
    $result = Order::with(['orderItems.item'])->first()->orderItems;
    dd($result);
});


Route::get('/logs/laravel', function () {
    $path = storage_path('logs/laravel.log');

    if (!File::exists($path)) {
        abort(404, "Log file not found.");
    }

    return Response::download($path, 'laravel.log', [
        'Content-Type' => 'text/plain',
    ]);
});
