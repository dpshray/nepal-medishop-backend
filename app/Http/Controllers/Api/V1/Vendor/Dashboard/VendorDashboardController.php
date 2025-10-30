<?php

namespace App\Http\Controllers\Api\V1\Vendor\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Purchase\Order;
use App\Models\VendorProductPrice;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorDashboardController extends Controller
{
    //
    use ResponseTrait;
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
        $total_uploaded_products_count = VendorProductPrice::where('product_vendor_id', $user->id)
            ->where('status', 1)
            ->count();

        //Total assigned orders to this vendor
        $total_assigned_orders_count = Order::where('assigned_vendor_id', $user->id)->count();

        //Total delivered orders by this vendor
        $total_delivered_orders_count = Order::where('assigned_vendor_id', $user->id)
            ->where('status', 'Delivered')
            ->count();
        $total_vendor_earning = Order::where('assigned_vendor_id', $user->id)
            ->where('status', 'Delivered')
            ->sum('price');

        $data = [
            'total_uploaded_products_count' => $total_uploaded_products_count,
            'total_assigned_orders_count'   => $total_assigned_orders_count,
            'total_delivered_orders_count'  => $total_delivered_orders_count,
            'total_vendor_earning'          => $total_vendor_earning,
        ];
        return $this->apiSuccess('Vendor dashboard retrieved successfully.', $data);
    }
}
