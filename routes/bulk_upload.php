<?php

use App\Http\Controllers\Api\V1\BulkUpload\BulkUploadMainController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('admin/bulk-upload')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->controller(BulkUploadMainController::class)
    ->group(function () {

    Route::post('product', 'productBulkUpload');
    Route::post('tag', 'tagBulkUpload');
    Route::post('category', 'categoryBulkUpload');
});
