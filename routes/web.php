<?php

use App\Models\Product;
use App\Models\ProductVendor;
use App\Models\Purchase\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('test', function(){
    $res = Order::find(3)->orderItems;
    $res->load('orderItemProducts');
    $val = [8];
    dd($res->pluck('orderItemProducts')->flatten()->toArray());
});

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

Route::get('/logs/laravel', function () {
    $path = storage_path('logs/laravel.log');

    if (!File::exists($path)) {
        abort(404, "Log file not found.");
    }

    return Response::download($path, 'laravel.log', [
        'Content-Type' => 'text/plain',
    ]);
});
