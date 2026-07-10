<?php

namespace App\Http\Controllers\Api\V1\Vendor\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Report\VendorSaleReportRequest;
use App\Models\ProductVendor;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\Vendor;
use App\Models\VendorProductPrice;
use App\Services\Report\Vendor\VendorSalesService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorDashboardController extends Controller
{
    //
    use ResponseTrait;
    public function __construct(private VendorSalesService $service) {}
    /**
     * @OA\Get(
     *     path="/vendor/dashboard",
     *     summary="Get vendor dashboard statistics",
     *     description="Retrieve vendor statistics including total uploaded products, assigned orders, delivered orders, and total earnings.",
     *     tags={"Vendor Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Vendor dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor dashboard statistics retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_uploaded_products_count", type="integer", example=25, description="Total products uploaded and accepted by admin"),
     *                 @OA\Property(property="total_assigned_orders_count", type="integer", example=8, description="Total number of orders assigned to the vendor"),
     *                 @OA\Property(property="total_delivered_orders_count", type="integer", example=5, description="Total number of delivered orders"),
     *                 @OA\Property(property="total_vendor_earning", type="number", format="float", example=15750.50, description="Total vendor earnings from delivered orders")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access"
     *     )
     * )
     */

    function index()
    {
        $user = Auth::user();
        $vendor = Vendor::where('user_id', $user->id)->first();
        $total_uploaded_products_count = ProductVendor::where('vendor_id', $vendor->id)
            ->where('status', 1)
            ->count();

        //Total assigned orders to this vendor
        $total_assigned_orders_count = OrderItem::where('assigned_vendor_id', $vendor->id)->count();

        //Total delivered orders by this vendor
        $total_delivered_orders_count = OrderItem::where('assigned_vendor_id', $vendor->id)
            ->where('status', 'DELIVERED')
            ->count();
        $total_vendor_earning = OrderItem::where('assigned_vendor_id', $vendor->id)
            ->where('status', 'DELIVERED')
            ->sum('total');

        $data = [
            'total_uploaded_products_count' => $total_uploaded_products_count,
            'total_assigned_orders_count'   => $total_assigned_orders_count,
            'total_delivered_orders_count'  => $total_delivered_orders_count,
            'total_vendor_earning'          => $total_vendor_earning,
        ];
        return $this->apiSuccess('Vendor dashboard retrieved successfully.', $data);
    }
    /**
     * @OA\Get(
     *     path="/vendor/dashboard-chart",
     *     summary="Get vendor dashboard statistics",
     *     description="Retrieve vendor statistics including total uploaded products, assigned orders, delivered orders, and total earnings.",
     *     tags={"Vendor Dashboard"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Vendor dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor dashboard statistics retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_uploaded_products_count", type="integer", example=25, description="Total products uploaded and accepted by admin"),
     *                 @OA\Property(property="total_assigned_orders_count", type="integer", example=8, description="Total number of orders assigned to the vendor"),
     *                 @OA\Property(property="total_delivered_orders_count", type="integer", example=5, description="Total number of delivered orders"),
     *                 @OA\Property(property="total_vendor_earning", type="number", format="float", example=15750.50, description="Total vendor earnings from delivered orders")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access"
     *     )
     * )
     */
    function viewchart(VendorSaleReportRequest $request)
    {
        $authuser = Auth::user()->id;
        $vendorId = Vendor::where('user_id', $authuser)->first()->id;
        $range   = $request->resolvedDateRange();
        $groupBy = $request->groupBy();
        $perPage = max(1, (int) $request->input('per_page', 25));

        $filters = $request->only([
            'category_id',
            'product_id',
            'order_status',
            'page',
        ]);
        $trend      = $this->service->getRevenueTrend($vendorId, $range['from'], $range['to'], $groupBy, $filters);
        return $this->apiSuccess('Vendor sale report fetched successfully', [
            'charts' => $trend,
        ]);
    }
}
