<?php

namespace App\Http\Controllers\Api\V1\Client\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\Order\UserOrderDetailResource;
use App\Http\Resources\User\Product\Order\UserOrderListResource;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientOrderController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Handle the incoming request.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/my-orders",
     *     summary="Fetch orders of a logged in user.",
     *     description="Fetch orders of a logged in user.NOTE: 
     *      payment_status values can be: PENDING, PAID, UNPAID, FAILED | 
     *      order_status values can be : PENDING, SHIPPED, DELIVERED",
     *     operationId="MyOrders",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="query",
     *         required=false,
     *         description="UUID of an order",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Pagination page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of orders."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="uuid", type="string", format="uuid", example="1961ab8b-dcd4-4d6b-b775-bbc524bf1e4f"),
     *                         @OA\Property(property="order_code", type="string", example="ZoNPT9RzFSkz1WPPjsWB"),
     *                         @OA\Property(property="address", type="string", example="Sitapaila, Kathmandu"),
     *                         @OA\Property(property="description", type="string", example="some description of this order COD"),
     *                         @OA\Property(property="price", type="number", format="float", example=31390.29),
     *                         @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                         @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                         @OA\Property(property="order_status", type="string", example="PENDING"),
     *                         @OA\Property(property="created_at", type="string", format="date", example="2025/10/14")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order detail retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order Detail."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_code", type="string", example="iApEu2O1fs479ftWP7GA"),
     *                 @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD"),
     *                 @OA\Property(property="price", type="number", format="float", example=31390.29),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                 @OA\Property(property="status", type="string", example="PENDING"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-16T09:38:05.000000Z"),
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="item_name", type="string", example="Debitis debitis autem consectetur saepe."),
     *                         @OA\Property(property="variant_name", type="string", nullable=true, example="Variant-2"),
     *                         @OA\Property(property="variant_size", type="string", nullable=true, example="200.00 patch"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=1385.28),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=2770.56)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $order_uuid = $request->query('uuid');
        if ($order_uuid) {
            $order = Order::where('uuid', $order_uuid)->firstOrFail();
            $order = new UserOrderDetailResource($order);
            return $this->apiSuccess('Order Detail.', $order);
        }        
        $pagination = Auth::user()->orders()->latest()->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => UserOrderListResource::collection($item))->data;
        return $this->apiSuccess('List of orders.', $data);
    }
}
