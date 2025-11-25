<?php

namespace App\Http\Controllers\Api\V1\Vendor\Order;

use App\Enums\Purchase\OrderItemStatusEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\PaymentMethodEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Events\LoyalityPointEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\Order\OrderAssignDetailResource;
use App\Http\Resources\Vendor\Order\OrderAssignListResource;
use App\Http\Resources\Vendor\Order\VendorVariantBatchNumberListResource;
use App\Models\ProductVariation;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItemProduct;
use App\Models\VendorProductPrice;
use App\Services\OrderService;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\UnauthorizedException;

class VendorOrderAssignController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Get(
     *     path="/vendor/orders",
     *     summary="Get list of assigned orders for the logged-in vendor",
     *     tags={"Vendor Orders"},
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
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of assigned orders retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of Assign Orders."),
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
    function index(Request $request)
    {
        $pagination = (new OrderService)->getListOfAssignedOrder($request);
        $data = $this->makePaginationResponse($pagination, fn($item) => OrderAssignListResource::collection($item))->data;
        return $this->apiSuccess('List of Assign Order Items.', $data);
    }
    /**
     *  @OA\Get(
     *     path="/vendor/orders/{order}",
     *     summary="Get detailed information about a specific assigned order",
     *     tags={"Vendor Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order UUID",
     *         @OA\Schema(type="string", example="55c9af7b-e7fb-4798-b3f6-3e76edc5cf2f")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order detail of an order assigned to this vendor.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Order detail of an order assigned to this vendor."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_code", type="string", example="MuEXEv"),
     *                 @OA\Property(property="user_type", type="string", example="GUEST"),
     *                 @OA\Property(property="name", type="string", example="James P. Sullivan"),
     *                 @OA\Property(property="email", type="string", example="james.sullivan100@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9844521125"),
     *                 @OA\Property(property="address", type="string", example="Kamalpokhari, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="2.52144"),
     *                 @OA\Property(property="longitude", type="string", example="18.21554"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD LZ"),
     *                 @OA\Property(property="price", type="number", format="float", example=22351.32),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                 @OA\Property(property="status", type="string", example="PENDING"),
     *                 @OA\Property(property="created_at", type="string", example="2025/11/24"),
     *     
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="prescription_required", type="boolean", example=true),
     *                         @OA\Property(property="prescription_image", type="string", nullable=true, example="http://example.com/image.jpg"),
     *     
     *                         @OA\Property(
     *                             property="item_products",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="OIP_ID", type="integer", example=9),
     *                                 @OA\Property(property="variant_name", type="string", example="Psz"),
     *                                 @OA\Property(property="product_name", type="string", example="Iste ea minus et dicta consequuntur."),
     *                                 @OA\Property(property="required_quantity", type="integer", example=3),
     *                                 @OA\Property(property="variant_id", type="integer", example=15),
     *                                 @OA\Property(property="assigned_batch_numbers", type="string", nullable=true, example=null),
     *     
     *                                 @OA\Property(
     *                                     property="batch_numbers",
     *                                     type="array",
     *                                     @OA\Items(
     *                                         type="object",
     *                                         @OA\Property(property="batch_number_id", type="integer", example=15),
     *                                         @OA\Property(property="quantity", type="integer", example=111),
     *                                         @OA\Property(property="batch_number", type="string", example="459962728")
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *     
     *                         @OA\Property(property="order_item_id", type="integer", example=5),
     *                         @OA\Property(property="quantity", type="integer", example=3),
     *                         @OA\Property(property="price", type="number", format="float", example=1350.44),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=4051.32)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function show(Order $order)
    {
        $order->load(['orderItems' => fn($qry) => $qry->with([
            'productVariant' => fn($qry) => $qry->with([
                'product',
                'vendorProductPrices' => fn($qry) =>$qry->whereRelation('ProductVendor','vendor_id',Auth::user()->vendor->id)
            ]),
            'item',
            'orderItemProducts.batchNumbers',
            'orderItemProducts.variation.product',
            'orderItemProducts.variation.vendorProductPrices' => fn($qry) => $qry->whereRelation('ProductVendor', 'vendor_id', Auth::user()->vendor->id),
        ])->where('assigned_vendor_id', Auth::user()->vendor->id)]);
        if ($order->orderItems->isEmpty()) {
            return $this->apiError('No order item has been assigned to you from this order.');
        }
        $order = new OrderAssignDetailResource($order);
        return $this->apiSuccess('Order detail of an order assigned to this vendor.', $order);
    }
    /** @OA\Put(
     *     path="/vendor/orders/{order}",
     *     summary="Update the status of an assigned order",
     *     description="Update the status of an assigned order.(values can be: 'PENDING','SHIPPED', 'DELIVERED')",
     *     tags={"Vendor Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string", example="55c9af7b-e7fb-4798-b3f6-3e76edc5cf2f")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="Order status",
     *                 enum={"PENDING","SHIPPED","DELIVERED"},
     *                 example="DELIVERED"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order has been update successfull")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid request data"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    function update(Order $order, Request $request)
    {
        //'in:Processing,Shipped,Delivered'
        $request->merge([
            'status' => strtoupper($request->input('status')) // example modification
        ]);
        $data = $request->validate([
            'status' => ['required',new Enum(OrderStatusEnum::class)]
        ]);
        // DB::transaction(function() use($order, $data){
            $status = strtoupper($data['status']);
            $status = OrderStatusEnum::from($status);
            $data = ['status' => $status];
            $order_items = $order->orderItems;
            if ($order_items->where('status', OrderItemStatusEnum::DELIVERED->value)->isNotEmpty()) {
                return $this->apiError('Cannot change order item status(Order items has already been delivered).');
            }

            if ($status === OrderStatusEnum::DELIVERED) {
                // $data = [...$data, ...['payment_status' => PaymentStatusEnum::PAID]];
                // event(new LoyalityPointEvent($order));
                $order->orderItems()->update(['status' => OrderItemStatusEnum::DELIVERED]);
            }
            /* else{
                $data = [...$data, ...['payment_status' => PaymentStatusEnum::UNPAID]];
            } */
            $order->refresh();
            $is_all_item_delivered = $order->orderItems
                ->whereIn('status', [
                    OrderItemStatusEnum::ASSIGNED->value, 
                    OrderItemStatusEnum::PENDING->value
                    ])
                ->isEmpty();
            if ($is_all_item_delivered) {
                if ($order->payment_method == PaymentMethodEnum::CASH_ON_DELIVERY->value) {
                    $data = [...$data, ...['payment_status' => PaymentStatusEnum::PAID]];
                    $order->update($data);
                }else{
                    $order->update(['status' => OrderStatusEnum::DELIVERED]);
                }
            }
        // });
        return $this->apiSuccess('Order has been changed to: '.strtolower($request->status));
    }

    /**
     * @OA\Get(
     *     path="/vendor/get-variant-batch-numbers/{id}",
     *     summary="Get list of batch numbers of a product variant",
     *     tags={"Vendor Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Variant ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of available variants batch.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of available variants batch."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="batch_number_id", type="integer", example=1),
     *                     @OA\Property(property="batch_number", type="string", example="776033374"),
     *                     @OA\Property(property="stock_left", type="integer", example=129)
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function fetchVariantBatchNumbers(Request $request, ProductVariation $variant) {
        $per_page = $request->query('per_page');
        $pagination = $variant->vendorProductPrices()
            ->whereRelation('ProductVendor','vendor_id', Auth::user()->vendor->id)
            ->when($per_page, fn($q) => $q->paginate($per_page), fn($q) => $q->get());
        if ($per_page) {
            $data = $this->makePaginationResponse($pagination, fn($item) => OrderAssignListResource::collection($item))->data;
        }else{
            $data = VendorVariantBatchNumberListResource::collection($pagination);
        }
        return $this->apiSuccess('List of available variants batch.', $data);
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/vendor/order-items/batch-assign/{uuid}",
     *     summary="Assign batch on order item product.",
     *     description="Assign batch on order item product.",
     *     tags={"Vendor Orders"},
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
    function assignBatchesToOrderItems(Order $order, Request $request) {
        $requested_data = $request->validate([
            '*.OIP_ID' => 'required|integer|exists:order_item_products,id',
            '*.batch_numbers' => 'required|array|min:1',
            '*.batch_numbers.*.batch_number_id' => 'required|integer|exists:vendor_product_prices,id',
            '*.batch_numbers.*.quantity' => 'required|integer|min:1',
        ]);
        // return $requested_data;
        
        $data = collect($requested_data)
            ->flatMap(function ($item) {
                return collect($item['batch_numbers'])->map(function ($bn) use ($item) {
                    return [
                        'order_item_product_id' => $item['OIP_ID'],
                        'vendor_product_price_id' => $bn['batch_number_id'],
                        'quantity'        => $bn['quantity'],
                    ];
                });
            })
            ->values();


        $temp = $data->groupBy('order_item_product_id')->map(fn($item) => [
            'order_item_product_id' => $item->first()['order_item_product_id'],
            'quantity' => $item->sum('quantity')
        ])->toArray();


        $order_items = $order->orderItems()
            ->with('orderItemProducts:id,order_item_id')
            ->where('assigned_vendor_id', Auth::user()->vendor->id)
            ->get();
        $is_already_been_assigned = $order_items->where('status', OrderItemStatusEnum::ASSIGNED->value)
            ->isNotEmpty();
        if ($is_already_been_assigned) {
            return $this->apiError('This order has already been assigned.');
        }
        $some_order_item_left_to_assign = $order_items->flatMap(fn($item) => $item->orderItemProducts)
            ->pluck('id')
            ->diff(array_keys($temp))
            ->count();
        if ($some_order_item_left_to_assign) {
            return $this->apiError('All items of this order must be assigned to continue.');
        }
        // return [array_diff($assigned_items_of_this_order, array_keys($temp))];

        $order_item_products_ids = collect($data)->pluck('order_item_product_id')->all();
        $order_item_products = OrderItemProduct::whereIn('id', $order_item_products_ids)
            ->get();
        $has_enough_quantity = $order_item_products->every(function($item) use($temp){
                return $item->quantity == $temp[$item->id]['quantity'];
            });
        if (!$has_enough_quantity) {
            return $this->apiError('Batch number quantity is no equal to required order quantity.');
        }
        $does_not_belong_to_same_order = $order_item_products->pluck('order_id')->unique()->count() != 1; 
        if ($does_not_belong_to_same_order) {
            return $this->apiError('Some item belongs to different order.');
        }
        $VPPs = collect($data)->pluck('quantity','vendor_product_price_id')->all();
        $have_sufficient_stock = VendorProductPrice::whereIn('id', array_keys($VPPs))
            ->get()
            ->every(fn($item) => $item->stock_left >= $VPPs[$item->id]);
        if (!$have_sufficient_stock) {
            return $this->apiError('Insufficien stock.');
        }
        // return $data->all();
        DB::transaction(function () use($order,$data, $order_item_products_ids){
            DB::table('order_item_product_batch_numbers')->whereIn('order_item_product_id', array_unique($order_item_products_ids))->delete();
            DB::table('order_item_product_batch_numbers')->insert($data->all());
            $order->orderItems()
                ->with('orderItemProducts:id,order_item_id')
                ->where('assigned_vendor_id', Auth::user()->vendor->id)
                ->update(['status' => OrderItemStatusEnum::ASSIGNED]);
        });
        return $this->apiSuccess('Batch number allocated successfully for assigned order item.');
    }
}
