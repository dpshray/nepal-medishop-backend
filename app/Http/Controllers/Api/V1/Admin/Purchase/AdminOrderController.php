<?php

namespace App\Http\Controllers\Api\V1\Admin\Purchase;

use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Purchase\AdminMyAssignedOrderDetailResource;
use App\Http\Resources\Admin\Purchase\AdminOrderDetailResource;
use App\Http\Resources\Admin\Purchase\AdminOwnAssignedOrderListResource;
use App\Http\Resources\Admin\Purchase\OrderListResource;
use App\Http\Resources\Admin\Vendor\Order\AdminVendorOrderAssignListResource;
use App\Models\Purchase\Order;
use App\Services\OrderService;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     *     @OA\Response(
     *         response=200,
     *         description="Order detail of an order assigned to this vendor.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order detail of an order assigned to this vendor."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_code", type="string", example="arNtWH"),
     *                 @OA\Property(property="user_type", type="string", example="USER"),
     *                 @OA\Property(property="name", type="string", example="user00"),
     *                 @OA\Property(property="email", type="string", example="user@gmail.com"),
     *                 @OA\Property(property="mobile", type="string", example="9882334586"),
     *                 @OA\Property(property="address", type="string", example="Lazimpat, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="2.52144"),
     *                 @OA\Property(property="longitude", type="string", example="18.21554"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD LZ"),
     *                 @OA\Property(property="price", type="number", example=3825.15),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                 @OA\Property(property="status", type="string", example="PENDING"),
     *                 @OA\Property(property="created_at", type="string", example="2025/11/25"),
    
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="prescription_required", type="boolean", example=false),
     *                         @OA\Property(property="prescription_image", type="string", nullable=true, example=null),
    
     *                         @OA\Property(
     *                             property="item_products",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="OIP_ID", type="integer", example=10),
     *                                 @OA\Property(property="variant_name", type="string", example="aXj"),
     *                                 @OA\Property(property="product_name", type="string", example="Omnis quod vel tempore dolorem consequatur."),
     *                                 @OA\Property(property="required_quantity", type="integer", example=3),
     *                                 @OA\Property(property="variant_id", type="integer", example=6),
    
     *                                 @OA\Property(
     *                                     property="assigned_batch_numbers",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="variant_id", type="integer", example=6),
     *                                         @OA\Property(property="batch_number", type="string", example="348927242"),
     *                                         @OA\Property(property="quantity", type="integer", example=1)
     *                                     )
     *                                 ),
    
     *                                 @OA\Property(
     *                                     property="batch_numbers",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="batch_number_id", type="integer", example=6),
     *                                         @OA\Property(property="quantity", type="integer", example=136),
     *                                         @OA\Property(property="batch_number", type="string", example="348927242")
     *                                     )
     *                                 )
     *                             )
     *                         ),
    
     *                         @OA\Property(property="order_item_id", type="integer", example=7),
     *                         @OA\Property(property="quantity", type="integer", example=3),
     *                         @OA\Property(property="price", type="number", example=193.05),
     *                         @OA\Property(property="subtotal", type="number", example=579.15)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function show(Order $order) {
        try {
            $order = (new OrderService)->showOrderDetail($order);
            $order = new AdminOrderDetailResource($order);
        } catch (OrderException $e) {
            return $this->apiError($e->getMessage());
        }
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



    /**
     * @OA\Get(
     *     path="/admin/admin-assigned-orders",
     *     summary="Get list of assigned orders of admin itself",
     *     tags={"Order"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page (default: 10)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search by customer name, email, mobile, or address",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of assigned orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of Assign Order Items(To Admin)."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="order_code", type="string", example="ORD-2025-001"),
     *                         @OA\Property(property="price", type="number", format="float", example=2500.75),
     *                         @OA\Property(property="user_name", type="string", example="John Doe"),
     *                         @OA\Property(property="user_email", type="string", example="john@example.com"),
     *                         @OA\Property(property="order_items_count", type="integer", example=3),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-20 12:30:00")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=5),
     *                 @OA\Property(property="total_items", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    function getAdminAssignedOrder(Request $request)
    {
        $pagination = (new OrderService)->getListOfAssignedOrder($request);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminOwnAssignedOrderListResource::collection($item))->data;
        return $this->apiSuccess('List of Assign Order Items(To Admin).', $data);
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/order-items/batch-assign/{uuid}",
     *     summary="Assign batch on order item product(ADMIN).",
     *     description="Assign batch on order item product(ADMIN).",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Order UUID",
     *         @OA\Schema(type="string", example="bc1b2da8-f8a2-4914-83bb-e4437ca655ad")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 required={"OIP_ID", "batch_numbers"},
     *                 
     *                 @OA\Property(
     *                     property="OIP_ID",
     *                     type="integer",
     *                     example=10,
     *                     description="Order Item Product ID"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="batch_numbers",
     *                     type="array",
     *                     description="List of batch numbers with quantities",
     *                     @OA\Items(
     *                         type="object",
     *                         required={"batch_number_id", "quantity"},
     *
     *                         @OA\Property(
     *                             property="batch_number_id",
     *                             type="integer",
     *                             example=120,
     *                             description="ID of the batch number"
     *                         ),
     *                         @OA\Property(
     *                             property="quantity",
     *                             type="integer",
     *                             example=2,
     *                             description="Quantity taken from this batch number"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch number allocated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Batch number allocated successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function assignBatchesToOrderItemsByAdmin(Request $request, Order $order) {
        $requested_data = $request->validate([
            '*.OIP_ID' => 'required|integer|exists:order_item_products,id',
            '*.batch_numbers' => 'required|array|min:1',
            '*.batch_numbers.*.batch_number_id' => 'required|integer|exists:vendor_product_prices,id',
            '*.batch_numbers.*.quantity' => 'required|integer|min:1',
        ]);
        // return $order->orderItemProducts;
        try {
            (new OrderService)->assignBatchToOrderItemService($order, $requested_data);
        } catch (OrderException $e) {
            return $this->apiError($e->getMessage());
        }
        return $this->apiSuccess('Batch number allocated successfully for order item(Admin).');
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/fetch-my-assigned-order-detail/{uuid}",
     *     summary="Get own assigned order details.",
     *     description="Get own assigned order details by UUID.",
     *     operationId="UserOrderOwnAssignedDetail",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order",
     *         @OA\Schema(type="string", example="5cc40466-e88d-4ab3-80d8-6274a4ecf4a3")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order Full Detail.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order Full Detail."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
    
     *                 @OA\Property(property="order_code", type="string", example="MuEXEv"),
     *                 @OA\Property(property="user_type", type="string", example="GUEST"),
     *                 @OA\Property(property="name", type="string", example="James P. Sullivan"),
     *                 @OA\Property(property="email", type="string", example="james.sullivan100@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9844521125"),
     *                 @OA\Property(property="address", type="string", example="Kamalpokhari, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="2.52144"),
     *                 @OA\Property(property="longitude", type="string", example="18.21554"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD LZ"),
     *                 @OA\Property(property="price", type="number", example=18000),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="payment_status", type="string", example="PAID"),
     *                 @OA\Property(property="status", type="string", example="DELIVERED"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", nullable=true, example=null),
     *                 @OA\Property(property="gift_wrap_charge", type="string", example="300.00"),
     *                 @OA\Property(property="created_at", type="string", example="2025/11/24"),
    
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
    
     *                         @OA\Property(property="type", type="string", example="package"),
     *                         @OA\Property(property="prescription_required", type="boolean", example=false),
     *                         @OA\Property(property="prescription_image", type="string", nullable=true, example=null),
    
     *                         @OA\Property(
     *                             property="item_products",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
    
     *                                 @OA\Property(property="OIP_ID", type="integer", example=7),
     *                                 @OA\Property(property="variant_id", type="integer", example=10),
     *                                 @OA\Property(property="product_name", type="string", example="Nihil qui enim quo laudantium voluptatem corporis."),
     *                                 @OA\Property(property="variant_name", type="string", example="YLV"),
     *                                 @OA\Property(property="required_quantity", type="integer", example=2),
    
     *                                 @OA\Property(
     *                                     property="assigned_batch_numbers",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="variant_id", type="integer", example=10),
     *                                         @OA\Property(property="batch_number", type="string", example="1548432970"),
     *                                         @OA\Property(property="quantity", type="integer", example=2)
     *                                     )
     *                                 ),
    
     *                                 @OA\Property(
     *                                     property="batch_numbers",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="batch_number_id", type="integer", example=96),
     *                                         @OA\Property(property="quantity", type="integer", example=130),
     *                                         @OA\Property(property="batch_number", type="string", example="1548432970")
     *                                     )
     *                                 )
     *                             )
     *                         ),
    
     *                         @OA\Property(property="order_item_id", type="integer", example=6),
     *                         @OA\Property(property="item_name", type="string", example="Family Set"),
     *                         @OA\Property(property="variant_name", type="string", nullable=true, example=null),
     *                         @OA\Property(property="variant_size", type="string", nullable=true, example=null),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=9000),
     *                         @OA\Property(property="subtotal", type="number", example=18000)
     *                     )
     *                 ),
    
     *                 @OA\Property(property="order_item_status", type="string", example="DELIVERED")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function getMyAssignedOrderDetail(Order $order) {
        try {
            $order = (new OrderService)->showOrderDetail($order,true);
            $order = new AdminMyAssignedOrderDetailResource($order);
        } catch (OrderException $e) {
            return $this->apiError($e->getMessage());
        }
        return $this->apiSuccess('Order Full Detail.', $order);
    }
}
