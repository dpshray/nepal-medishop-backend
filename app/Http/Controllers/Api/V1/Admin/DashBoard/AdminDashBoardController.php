<?php

namespace App\Http\Controllers\Api\V1\Admin\DashBoard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\Product\Service\ServiceBooking;
use App\Models\ProductVendor;
use App\Models\Purchase\Order;
use App\Models\Tag;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminDashBoardController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Get(
     *     path="/admin/main-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Main Dashboard",
     *     description="Main Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Main Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Main dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_orders",
     *                     type="integer",
     *                     example=100
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_packages",
     *                     type="integer",
     *                     example=50
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_products",
     *                     type="integer",
     *                     example=1000
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_brands",
     *                     type="integer",
     *                     example=200
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_categories",
     *                     type="integer",
     *                     example=150
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_tags",
     *                     type="integer",
     *                     example=100
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_users",
     *                     type="integer",
     *                     example=10000
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_vendors",
     *                     type="integer",
     *                     example=500
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function main()
    {
        $total_orders = Order::count();
        $total_packages = Package::count();
        $total_products = Product::count();
        $total_brands = Brand::count();
        $total_categories = Category::count();
        $total_tags = Tag::count();
        $total_users = User::where('user_type', 3)->count();
        $total_vendors = User::where('user_type', 2)->count();
        $data = [
            'total_orders' => $total_orders,
            'total_packages' => $total_packages,
            'total_products' => $total_products,
            'total_brands' => $total_brands,
            'total_categories' => $total_categories,
            'total_tags' => $total_tags,
            'total_users' => $total_users,
            'total_vendors' => $total_vendors,
        ];
        return $this->apiSuccess('Main Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/user-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get User Dashboard",
     *     description="User Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_user",
     *                     type="integer",
     *                     example=100
     *                 ),
     *
     *                 @OA\Property(
     *                     property="active_users",
     *                     type="integer",
     *                     example=80
     *                 ),
     *
     *                 @OA\Property(
     *                     property="inactive_users",
     *                     type="integer",
     *                     example=20
     *                 ),
     *
     *                 @OA\Property(
     *                     property="new_users_month",
     *                     type="integer",
     *                     example=15
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function user()
    {
        $total_client = User::where('user_type', 3)->count();
        $active_users = User::where('user_type', 3)->where('status', 1)->count();
        $inactive_users = User::where('user_type', 3)->where('status', 0)->count();
        $new_users_month = User::where('user_type', 3)->where('created_at', '>=', now()->startOfMonth())->count();
        $data = [
            'total_user' => $total_client,
            'active_users' => $active_users,
            'inactive_users' => $inactive_users,
            'new_users_month' => $new_users_month,
        ];
        return $this->apiSuccess('User Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/vendor-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Vendor Dashboard",
     *     description="Vendor Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Vendor dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_vendor",
     *                     type="integer",
     *                     example=50
     *                 ),
     *
     *                 @OA\Property(
     *                     property="active_vendors",
     *                     type="integer",
     *                     example=40
     *                 ),
     *
     *                 @OA\Property(
     *                     property="inactive_vendors",
     *                     type="integer",
     *                     example=10
     *                 ),
     *
     *                 @OA\Property(
     *                     property="new_vendors_month",
     *                     type="integer",
     *                     example=5
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function vendor()
    {
        $total_vendors = User::where('user_type', 2)->count();
        $active_vendors = User::where('user_type', 2)->where('status', 1)->count();
        $inactive_vendors = User::where('user_type', 2)->where('status', 0)->count();
        $new_vendors_month = User::where('user_type', 2)->where('created_at', '>=', now()->startOfMonth())->count();
        $data = [
            'total_vendor' => $total_vendors,
            'active_vendors' => $active_vendors,
            'inactive_vendors' => $inactive_vendors,
            'new_vendors_month' => $new_vendors_month,
        ];
        return $this->apiSuccess('Vendor Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/brand-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Brand Dashboard",
     *     description="Brand Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Brand Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Brand dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_brands",
     *                     type="integer",
     *                     example=50
     *                 ),
     *
     *                 @OA\Property(
     *                     property="active_brands",
     *                     type="integer",
     *                     example=40
     *                 ),
     *
     *                 @OA\Property(
     *                     property="inactive_brands",
     *                     type="integer",
     *                     example=10
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_featured",
     *                     type="integer",
     *                     example=5
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_popular",
     *                     type="integer",
     *                     example=5
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function brand()
    {
        $total_brands = Brand::count();
        $active_brands = Brand::where('status', 1)->count();
        $inactive_brands = Brand::where('status', 0)->count();
        $total_featured = Brand::where('is_featured', 1)->count();
        $total_popular = Brand::where('is_popular', 1)->count();
        $data = [
            'total_brands' => $total_brands,
            'active_brands' => $active_brands,
            'inactive_brands' => $inactive_brands,
            'total_featured' => $total_featured,
            'total_popular' => $total_popular,
        ];
        return $this->apiSuccess('Brand Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/product-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Product Dashboard",
     *     description="Product Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_products",
     *                     type="integer",
     *                     example=100
     *                 ),
     *
     *                 @OA\Property(
     *                     property="active_products",
     *                     type="integer",
     *                     example=80
     *                 ),
     *
     *                 @OA\Property(
     *                     property="inactive_products",
     *                     type="integer",
     *                     example=20
     *                 ),
     *
     *                 @OA\Property(
     *                     property="new_products_month",
     *                     type="integer",
     *                     example=15
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function product()
    {
        $total_products = Product::count();
        $active_products = Product::where('status', 1)->count();
        $inactive_products = Product::where('status', 0)->count();
        $new_products_month = Product::where('created_at', '>=', now()->startOfMonth())->count();
        $data = [
            'total_products' => $total_products,
            'active_products' => $active_products,
            'inactive_products' => $inactive_products,
            'new_products_month' => $new_products_month,
        ];
        return $this->apiSuccess('Product Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/package-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Package Dashboard",
     *     description="Package Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Package Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Package dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_packages",
     *                     type="integer",
     *                     example=50
     *                 ),
     *
     *                 @OA\Property(
     *                     property="active_packages",
     *                     type="integer",
     *                     example=40
     *                 ),
     *
     *                 @OA\Property(
     *                     property="inactive_packages",
     *                     type="integer",
     *                     example=10
     *                 ),
     *             )
     *         )
     *     )
     * )
     */
    function package()
    {
        $total_packages = Package::count();
        $active_packages = Package::where('status', 1)->count();
        $inactive_packages = Package::where('status', 0)->count();
        $data = [
            'total_packages' => $total_packages,
            'active_packages' => $active_packages,
            'inactive_packages' => $inactive_packages,
        ];
        return $this->apiSuccess('Package Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/order-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Order Dashboard",
     *     description="Order Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order Dashboard",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order dashboard data fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_orders",
     *                     type="integer",
     *                     example=100
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_pending_orders",
     *                     type="integer",
     *                     example=80
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_delivered_orders",
     *                     type="integer",
     *                     example=20
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_cancelled_orders",
     *                     type="integer",
     *                     example=5
     *                 ),
     *             )
     *         )
     *     )
     * )
     */
    function order()
    {
        $total_orders = Order::count();
        $total_pending_orders = Order::where('status', 'PENDING')->count();
        $total_delivered_orders = Order::where('status', 'DELIVERED')->count();
        $total_cancelled_orders = Order::where('status', 'CANCELLED')->count();
        $data = [
            'total_orders' => $total_orders,
            'total_pending_orders' => $total_pending_orders,
            'total_delivered_orders' => $total_delivered_orders,
            'total_cancelled_orders' => $total_cancelled_orders,
        ];
        return $this->apiSuccess('Order Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/product-request-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Product Request Dashboard",
     *     description="Product Request Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product Request Dashboard",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product Request dashboard data fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_product_request", type="integer", example=100),
     *                 @OA\Property(property="total_pending_product_request", type="integer", example=80),
     *                 @OA\Property(property="total_approved_product_request", type="integer", example=20),
     *                 @OA\Property(property="total_rejected_product_request", type="integer", example=5),
     *             )
     *         )
     *     )
     * )
     */
    function product_request()
    {
        $total_product_request = ProductVendor::count();
        $total_pending_product_request = ProductVendor::where('status', '0')->count();
        $total_approved_product_request = ProductVendor::where('status', '1')->count();
        $data = [
            'total_product_request' => $total_product_request,
            'total_pending_product_request' => $total_pending_product_request,
            'total_approved_product_request' => $total_approved_product_request,
        ];
        return $this->apiSuccess('Product Request Dashboard', $data);
    }
    /**
     * @OA\Get(
     *     path="/admin/service-booking-dashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get Service Booking Dashboard",
     *     description="Service Booking Dashboard statistics",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Service Booking Dashboard",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service Booking dashboard data fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_service_booking", type="integer", example=100),
     *                 @OA\Property(property="total_pending_service_booking", type="integer", example=80),
     *                 @OA\Property(property="total_approved_service_booking", type="integer", example=20),
     *                 @OA\Property(property="total_rejected_service_booking", type="integer", example=5),
     *             )
     *         )
     *     )
     * )
     */
    function service_booking_dashboard()
    {
        $total_service_booking = ServiceBooking::count();
        $total_pending_service_booking = ServiceBooking::where('status', 'PENDING')->count();
        $total_approved_service_booking = ServiceBooking::where('status', 'DELIVERED')->count();
        $total_cancelled_service_booking = ServiceBooking::where('status', 'CANCELLED')->count();
        $data = [
            'total_service_booking' => $total_service_booking,
            'total_pending_service_booking' => $total_pending_service_booking,
            'total_approved_service_booking' => $total_approved_service_booking,
            'total_cancelled_service_booking' => $total_cancelled_service_booking,
        ];
        return $this->apiSuccess('Service Booking Dashboard', $data);
    }
}
