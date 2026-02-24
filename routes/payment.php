<?php

use App\Http\Controllers\Api\V1\Payment\Esewa\EsewaController;
use Illuminate\Support\Facades\Route;

// eSewa Payment Routes
Route::prefix('payment/esewa')->group(function () {
    Route::post('initiate', [EsewaController::class, 'initiate']);
    Route::get('success', [EsewaController::class, 'success'])->name('api.esewa.success');
    Route::get('failure', [EsewaController::class, 'failure'])->name('api.esewa.failure');
    Route::post('verify', [EsewaController::class, 'verify']);
});
