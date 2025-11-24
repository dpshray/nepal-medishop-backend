<?php

namespace App\Http\Controllers\Api\V1\Admin\Purchase;

use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Purchase\AdminOrderDetailResource;
use App\Http\Resources\Admin\Purchase\OrderListResource;
use App\Models\Purchase\Order;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/user-order",
     *     summary="Get all user orders.",
     *     description="Get all user orders.
     *      payment_method: Cash on Delivery | 
     *      payment_status: PENDING, PAID, FAILED |
     *      status: PENDING, SHIPPED, DELIVERED",
     *     operationId="UserOrderList",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number of list",
     *         @OA\Schema(type="integer", example=1)
     *     ),     
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items on each page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search an order based on name/email/mobile/address",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of orders."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="order_uuid", type="string", example="54cb29e1-061d-4a2f-bd99-f17ffc2e75cd"),
     *                         @OA\Property(property="payment_method", type="string", example="Google Pay"),
     *                         @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                         @OA\Property(property="status", type="string", example="DELIVERED"),
     *                         @OA\Property(property="no_of_ordered_items", type="integer", example=2),
     *                         @OA\Property(property="git_wrap", type="boolean", example=true),
     *                         @OA\Property(property="order_code", type="string", example="1PO91o89DV5qqKnQdRSb"),
     *                         @OA\Property(property="name", type="string", example="user00"),
     *                         @OA\Property(property="email", type="string", example="user@gmail.com"),
     *                         @OA\Property(property="mobile", type="string", example="9870396296"),
     *                         @OA\Property(property="address", type="string", example="ghar ma")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=14),
     *                 @OA\Property(property="total_items", type="integer", example=14)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function index(Request $request) {
        $per_page = $request->query('per_page',Order::count());
        $search = $request->query('search');

        $pagination = Order::with(['user'])
            ->withCount('orderItems')
            ->when($search, function($qry) use($request){
                $qry->where(function($q) use($request){
                    $q->whereLike('name', '%'.$request->search.'%')
                    ->orWhereLike('email', '%'.$request->search.'%')
                    ->orWhereLike('mobile', '%'.$request->search.'%')
                    ->orWhereLike('address', '%'.$request->search.'%');
                });
            })
            ->latest()
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => OrderListResource::collection($item))->data;
        return $this->apiSuccess('List of orders.', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/user-order/{uuid}",
     *     summary="Get user order details.",
     *     description="Fetch detailed information of a specific user order by UUID.",
     *     operationId="UserOrderDetail",
     *     tags={"Order"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order",
     *         @OA\Schema(type="string", example="5cc40466-e88d-4ab3-80d8-6274a4ecf4a3")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order Detail Response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order Detail."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_code", type="string", example="hNdz0Ck2k6E0ioxUH6qH"),
     *                 @OA\Property(property="user_type", type="string", example="USER"),
     *                 @OA\Property(property="name", type="string", example="user00"),
     *                 @OA\Property(property="email", type="string", example="user@gmail.com"),
     *                 @OA\Property(property="mobile", type="string", example="9835064312"),
     *                 @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="77.52144"),
     *                 @OA\Property(property="longitude", type="string", example="18.21554"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD"),
     *                 @OA\Property(property="price", type="number", format="float", example=1761.1),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper."),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                 @OA\Property(property="status", type="string", example="PENDING"),
     *                 @OA\Property(property="created_at", type="string", example="2025/11/13"),
     *
     *                 @OA\Property(
     *                     property="order_assigned_to",
     *                     type="object",
     *                     @OA\Property(property="store_name", type="string", example="Lockman Ltd"),
     *                     @OA\Property(property="email", type="string", example="vendor@gmail.com")
     *                 ),
     *
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="prescription_required", type="boolean", example=false),
     *                         @OA\Property(property="prescription_image", type="string", nullable=true, example=null),
     *                         @OA\Property(property="item_name", type="string", example="Quia velit sed quia repellendus."),
     *                         @OA\Property(property="variant_name", type="string", example="Variant-1"),
     *                         @OA\Property(property="variant_size", type="string", example="100.00 IU"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=100.7),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=201.4)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
    */
    function show(Order $order) {
        $order->load(['orderItems.assignedVendor', 'orderItems']);
        $order = new AdminOrderDetailResource($order);
        return $this->apiSuccess('Order Detail.', $order);
    }

    function update(Request $request, Order $order) {
        // return $order;
        // return $request->all();
        $request->validate([
            'payment_status' => ['sometimes','nullable', Rule::in(PaymentStatusEnum::paymentUpdateValues())],
            'order_status' => ['sometimes','nullable', Rule::in(array_map(fn($item) => strtolower($item->value), OrderStatusEnum::cases()))],
        ]);
        if ($request->payment_status) {
            
        }
        if ($request->order_status) {
            
        }
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/user-order/{uuid}",
     *     operationId="DeleteOrder",
     *     tags={"Order"},
     *     summary="Delete Order",
     *     description="Delete an order by UUID.",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of an order",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order has been deleted.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order has been deleted."),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function destroy(Request $request, Order $order) {
        $order->delete();
        return $this->apiSuccess('Order has been deleted.');
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/orders/{uuid}/cancel-order",
     *     summary="Cancel an order based in order uuid.",
     *     description="Cancell an order based in order uuid.",
     *     operationId="UserOrderCancell",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of an order",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancellation response.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order has been cancelled."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function cancelUserOrder(Order $order) {
        $order->update(['status' => OrderStatusEnum::CANCELLED]);
        return $this->apiSuccess('Order has been cancelled.');
    }
}
