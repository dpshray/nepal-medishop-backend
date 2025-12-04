<?php

use App\Enums\AdminUrlParamEnum;
use App\Enums\RouteParamEnum;
use App\Http\Controllers\Api\V1\Admin\AdminSharedController;
use App\Http\Controllers\Api\V1\Admin\AdminVendorController;
use App\Http\Controllers\Api\V1\Admin\Banner\AdminBannerController;
use App\Http\Controllers\Api\V1\Admin\ClientFeedback\AdminFeedbackController;
use App\Http\Controllers\Api\V1\Admin\ClientPrescription\AdminPrescriptionController;
use App\Http\Controllers\Api\V1\Admin\Package\AdminPackageController;
use App\Http\Controllers\Api\V1\Admin\Point\AdminCouponPointController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminBrandController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminGenericProductNameController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminHealthConditionController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\Product\AdminTagController;
use App\Http\Controllers\Api\V1\Admin\Product\Service\AdminServiceBookingController;
use App\Http\Controllers\Api\V1\Admin\Product\Service\AdminServiceCategoryController;
use App\Http\Controllers\Api\V1\Admin\Product\Service\AdminServiceController;
use App\Http\Controllers\Api\V1\Admin\Product\Service\AdminServiceTagController;
use App\Http\Controllers\Api\V1\Admin\Product\Service\AdminVendorServiceController;
use App\Http\Controllers\Api\V1\Admin\PromoCode\AdminPromoCodeControlller;
use App\Http\Controllers\Api\V1\Admin\Purchase\AdminKitbagOrderController;
use App\Http\Controllers\Api\V1\Admin\Vendor\AdminVendorProductController;
use App\Http\Controllers\Api\V1\Admin\Purchase\AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\User\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\Vendor\OrderAssign\AdminOrderAssignController;
use App\Http\Controllers\Api\V1\Purchase\AdminCODController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(function(){
        Route::apiResource('vendor', AdminVendorController::class)->parameters(['vendor' => 'user'])->scoped(['user' => 'uuid']);
        Route::get('fetch-vendor-products/{user:uuid}', [AdminVendorController::class, 'getVendorProduct']);
        Route::controller(AdminVendorController::class)->group(function(){
            Route::get('vendor-verified-toggler/{user:uuid}', 'toggleVendorVerifiedStatus');
        });
        Route::apiResource('brand', AdminBrandController::class);
        Route::apiResource('category', AdminCategoryController::class);
        Route::get('category-menu-list', [AdminCategoryController::class, 'categoryMenuListFetcher']);
        Route::post('category-menu-manager', [AdminCategoryController::class, 'categoryMenuHandler']);
        Route::apiResource('tag', AdminTagController::class);
        Route::apiResource('health-condition', AdminHealthConditionController::class)->scoped(['health_condition' => 'slug']);
        Route::get('toggle-brand-status/{brand:slug}', [AdminBrandController::class, 'statusToggler']);
        Route::get('toggle-category-status/{category:slug}', [AdminCategoryController::class, 'statusToggler']);
        Route::get('toggle-tag-status/{tag:slug}', [AdminTagController::class, 'statusToggler']);
        /*----------  Product  ----------*/
        Route::apiResource('product', AdminProductController::class)->scoped(['product' => 'uuid']);
        Route::get('toggle-product-status/{product:uuid}', [AdminProductController::class, 'statusToggler']);
        Route::post('product-media/{product:uuid}', [AdminProductController::class, 'storeMedia']);
        Route::get('product/{product:uuid}/vendors', [AdminProductController::class, 'productVendors']);
        Route::get('product-units', [AdminProductController::class, 'productUnits']);
        /*----------  Package  ----------*/
        Route::apiResource('package',AdminPackageController::class)->scoped(['package' => 'slug']);
        Route::post('package/{slug}/add-product',[AdminPackageController::class,'add_product_to_package']);
        Route::post('package/{slug}/update-product',[AdminPackageController::class,'update_package_product']);
        Route::delete('package/{slug}/products',[AdminPackageController::class,'deleteProductFromPackage']);
        /*----------  Vendor  ----------*/
        Route::get('vendorproductlist',[AdminVendorProductController::class,'vendorProductList']);
        Route::patch('vendor-product-prices/{id}/approve', [AdminVendorProductController::class, 'approveVendorProduct']);
        Route::delete('vendor-product-prices/{id}', [AdminVendorProductController::class, 'deleteVendorProduct']);
        Route::get('vendor-product-prices-detail/{id}', [AdminVendorProductController::class, 'detail']);
        Route::apiResource('user-order', AdminOrderController::class)->parameters(['user-order' => 'order'])->scoped(['order' => 'uuid'])->except(['store']);
        Route::get('fetch-my-assigned-order-detail/{order:uuid}', [AdminOrderController::class, 'getMyAssignedOrderDetail']);
        /*----------  Order and Order Assign  ----------*/
        Route::get('orders/{order:uuid}/cancel-order', [AdminOrderController::class, 'cancelUserOrder']);
        Route::post('order-items/batch-assign/{order:uuid}', [AdminOrderController::class, 'assignBatchesToOrderItemsByAdmin']);
        Route::get('orders/{order:uuid}/vendors', [AdminOrderAssignController::class, 'getVendorsWithAssignability']);
        // Route::get('order/{order_uuid}', [AdminOrderController::class, 'cancelUserOrder']);
        Route::post('order/{order_uuid}/assign/{vendor_uuid}', [AdminOrderAssignController::class, 'AssignOrder']);
        Route::post('order/{order:uuid}/assign-to-admin', [AdminOrderAssignController::class, 'AssignOrderToAdmin']);
        Route::post('order/{order:uuid}/cancel-assign', [AdminOrderAssignController::class, 'CancelAssignOrder']);
        Route::get('admin-assigned-orders', [AdminOrderController::class, 'getAdminAssignedOrder']);
        /*----------  Service  ----------*/
        Route::apiResource('service-category', AdminServiceCategoryController::class)->scoped(['service_category' => 'slug']);
        Route::apiResource('service-tag', AdminServiceTagController::class)->scoped(['service_tag' => 'slug']);
        Route::apiResource('service', AdminServiceController::class)->scoped(['service' => 'slug']);
        Route::apiResource('service.vendor', AdminVendorServiceController::class)->except(['destroy','store'])->scoped(['service' => 'slug', 'vendor' => 'uuid']);
        Route::get('fetch-all-service-vendor', [AdminVendorServiceController::class, 'allServiceVendor']);
        /*----------  Service Booking and Assign  ----------*/
        Route::apiResource('service-booking', AdminServiceBookingController::class)->only(['index','show'])->scoped(['service_booking' => 'uuid']);
        Route::get('assign-booking/{service_booking:uuid}/vendor/{uuid}', [AdminServiceBookingController::class, 'assignServiceBookingToVendor']);
        /*----------  User Side  ----------*/
        Route::apiResource('users',AdminUserController::class)->except(['update','store','destroy'])->scoped(['user' => 'uuid']);
        Route::apiResource('banner', AdminBannerController::class);
        Route::apiResource('banner', AdminBannerController::class)->scoped(['banner' => 'uuid']);
        Route::get('toggle-banner-status/{banner:uuid}', [AdminBannerController::class, 'visibilityToggler']);
        Route::apiResource('kitbag', AdminKitbagOrderController::class)->only(['index','show','destroy'])->scoped(['kitbag' => 'uuid']);
        Route::apiResource('clientfeedback',AdminFeedbackController::class)->only(['index']);
        Route::apiResource('coupon',AdminPromoCodeControlller::class)->except(['show'])->scoped(['coupon'=>'uuid']);
        // Route::apiResource('coupon-point', AdminCouponPointController::class);
        Route::apiResource('generic-product-name', AdminGenericProductNameController::class)->scoped(['generic_product_name' => 'slug']);
        Route::apiResource('coupon',AdminPromoCodeControlller::class)->scoped(['coupon'=>'uuid']);
        Route::apiResource('prescription',AdminPrescriptionController::class)->only(['index','destroy']);
});
