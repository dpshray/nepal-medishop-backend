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
     *                         type="object",
     *                         @OA\Property(property="uuid", type="string", format="uuid", example="f739dfec-9083-4c80-ba12-9d7b820f3b96"),
     *                         @OA\Property(property="order_code", type="string", example="l0eOi9EqRI45NRqWdJMZ"),
     *                         @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *                         @OA\Property(property="description", type="string", example="some description of this order COD"),
     *                         @OA\Property(property="price", type="number", format="float", example=4712.54),
     *                         @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                         @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                         @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                         @OA\Property(property="order_status", type="string", example="PENDING"),
     *                         @OA\Property(property="created_at", type="string", example="2025/10/30")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=2)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $pagination = Auth::user()->orders()->with('promoCode')->latest()->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => UserOrderListResource::collection($item))->data;
        return $this->apiSuccess('List of orders.', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/my-order-detail/{uuid}",
     *     summary="Fetch order detail of a logged in user.",
     *     description="Fetch order detail of a logged in user",
     *     operationId="MyOrderDetail",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=false,
     *         description="UUID of an order",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order Detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order Detail."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="order_code", type="string", example="uPVydlBqN7WQQDC5dQVq"),
     *                 @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD"),
     *                 @OA\Property(property="price", type="number", format="float", example=9696),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper."),
     *                 @OA\Property(property="gift_wrap_charge", type="number", format="float", example=300),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="payment_status", type="string", example="UNPAID"),
     *                 @OA\Property(property="status", type="string", example="PENDING"),
     *                 @OA\Property(property="created_at", type="string", format="date", example="2025/11/04"),
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/storage/106/syrup.jpg"),
     *                         @OA\Property(property="item_name", type="string", example="Saepe reiciendis et quae dolores et tenetur voluptas."),
     *                         @OA\Property(property="item_slug", type="string", example="saepe-reiciendis-et-quae-dolores-et-tenetur-voluptas"),
     *                         @OA\Property(property="variant_name", type="string", nullable=true, example="Variant-1"),
     *                         @OA\Property(property="variant_size", type="string", nullable=true, example="100.00 g"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=198),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=396),
     *                         @OA\Property(
     *                             property="my_reviews",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/assets/img/user-profile-default.png"),
     *                                 @OA\Property(property="comment_uuid", type="string", format="uuid", example="ed58fd11-477b-42fb-86c1-e91b7082beba"),
     *                                 @OA\Property(property="user_name", type="string", example="user00"),
     *                                 @OA\Property(property="user_email", type="string", example="user@gmail.com"),
     *                                 @OA\Property(property="review", type="string", example="Beautiful, beauti--FUL SOUP!' 'Chorus again!' ..."),
     *                                 @OA\Property(property="rating", type="integer", example=5),
     *                                 @OA\Property(
     *                                     property="user_type",
     *                                     type="object",
     *                                     @OA\Property(property="user_type", type="integer", example=3),
     *                                     @OA\Property(property="label", type="string", example="USER")
     *                                 ),
     *                                 @OA\Property(property="review_date", type="string", example="30 Oct 2025"),
     *                                 @OA\Property(property="is_review_edited", type="boolean", example=true)
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function orderDetail(Request $request, Order $order)
    {
        $order->load([
            'orderItems' => [
                'product.reviews' => fn($qry) => $qry->with('user')->where('user_id', Auth::id()),
                'package.reviews' => fn($qry) => $qry->with('user')->where('user_id', Auth::id()),
            ],
            'promoCode',
            'orderItems.productVariant'
        ]);
        $order = new UserOrderDetailResource($order);
        return $this->apiSuccess('Order Detail.', $order);
    }
}
