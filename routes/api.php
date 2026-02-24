<?php

use App\Http\Controllers\Api\V1\Admin\Purchase\NCM\AdminNCMWebHookController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    require __DIR__ . '/auth.php';
    require __DIR__ . '/admin.php';
    require __DIR__ . '/vendor.php';
    require __DIR__ . '/client.php';
    require __DIR__ . '/bulk_upload.php';
    require __DIR__ . '/payment.php';
});
Route::post('/ncm-webhook', [AdminNCMWebHookController::class, 'WebHook'])
    ->name('ncm.webhook');
