<?php

namespace App\Http\Controllers\Api\V1\Client\Purchase;

use App\Enums\Purchase\OrderTypeEnum;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Purchase\CODRequest;
use App\Http\Requests\Client\Purchase\KitbagRequest;
use App\Services\OrderService;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;

class CODPurchaseController extends Controller
{
    use ResponseTrait;

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Submit an order(Regular).",
     *     description="Submit an order.NOTE: name, email, mobile fields are only needed for GUEST USER.",
     *     operationId="CODOrder",
     *     tags={"Order"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"payment_method","name","email","mobile","address"},
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="name", type="string", example="James P. Sullivan"),
     *                 @OA\Property(property="email", type="string", example="james.sullivan100@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9854112547"),
     *                 @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="1.201255"),
     *                 @OA\Property(property="longitude", type="string", example="22.25458"),
     *                 @OA\Property(property="description", type="string", example="some description of this order COD"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper."),
     *
     *                 @OA\Property(property="code", type="string", example="22.Test"),
     *
     *                 @OA\Property(property="products[0][product_slug]", type="string", example="unde-a-maiores-et-omnis"),
     *                 @OA\Property(property="products[0][variant_id]", type="integer", example=2),
     *                 @OA\Property(property="products[0][quantity]", type="integer", example=1),
     *                 @OA\Property(property="products[0][prescription_image]", type="string", format="binary"),
     *
     *                 @OA\Property(property="products[1][product_slug]", type="string", example="lorem-ipsum-product"),
     *                 @OA\Property(property="products[1][variant_id]", type="integer", example=5),
     *                 @OA\Property(property="products[1][quantity]", type="integer", example=3),
     *                 @OA\Property(property="products[1][prescription_image]", type="string", format="binary"),
     *
     *                 @OA\Property(property="packages[0][package_slug]", type="string", example="deluxe-box"),
     *                 @OA\Property(property="packages[0][quantity]", type="integer", example=1),
     *                 @OA\Property(property="packages[1][package_slug]", type="string", example="super-box"),
     *                 @OA\Property(property="packages[1][quantity]", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order placed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your order has been placed successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="previous_price", type="number", example=18820),
     *                 @OA\Property(property="amount", type="number", example=18820),
     *                 @OA\Property(property="order_number", type="string", example="9b9Xn9"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="date", type="string", example="2025/11/22"),
     *                 @OA\Property(property="delivery_address", type="string", example="Lazimpat, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="2.52144"),
     *                 @OA\Property(property="longitude", type="string", example="18.21554"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper."),
     *                 @OA\Property(property="gift_wrap_charge", type="number", example=300),
     *                 @OA\Property(property="promo_code", type="string", nullable=true, example=null),
     *                 @OA\Property(property="promo_discount", type="number", example=0),
     *     
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_name", type="string", example="Recusandae consequuntur earum nesciunt facilis cupiditate voluptatum non amet."),
     *                         @OA\Property(property="variant_name", type="string", nullable=true, example="Variant-8"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=4310),
     *                         @OA\Property(property="total", type="number", example=8620),
     *                         @OA\Property(property="prescription_image", type="string", example="")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function regularOrder(CODRequest $request)
    {
        if (!$request->hasAny(['products', 'packages'])) {
            return $this->apiError("At least one product or package must be included in the order.", 422);
        }
        Log::info($request->all());
        try {
            $response = (new OrderService)->saveOrder($request, OrderTypeEnum::REGULAR);
        } catch (OrderException $e) {
            return $this->apiError($e->getMessage());
        }

        return $this->apiSuccess("Your order has been placed successfully.", $response);
    }

    /**
     * @OA\Post(
     *     path="/kitbag-orders",
     *     summary="Submit an order(Kitbag | Only product can be added).",
     *     description="Submit an order.NOTE: name, email, mobile fields are only needed for GUEST USER.",
     *     operationId="CODKitbagOrder",
     *     tags={"Order"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"payment_method","name","email","mobile","address"},
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="name", type="string", example="Mathilda Monroe"),
     *                 @OA\Property(property="email", type="string", example="mathilda.monroe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9855412544"),
     *                 @OA\Property(property="address", type="string", example="Maharajgunj, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="88.201255"),
     *                 @OA\Property(property="longitude", type="string", example="12.25458"),
     *                 @OA\Property(property="description", type="string", example="some description of this kitbag order COD"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=false),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper with pink ribbon."),
     *
     *                 @OA\Property(property="products[0][product_slug]", type="string", example="unde-a-maiores-et-omnis"),
     *                 @OA\Property(property="products[0][variant_id]", type="integer", example=2),
     *                 @OA\Property(property="products[0][quantity]", type="integer", example=1),
     *                 @OA\Property(property="products[0][prescription_image]", type="string", format="binary"),
     *
     *                 @OA\Property(property="products[1][product_slug]", type="string", example="lorem-ipsum-product"),
     *                 @OA\Property(property="products[1][variant_id]", type="integer", example=5),
     *                 @OA\Property(property="products[1][quantity]", type="integer", example=3),
     *                 @OA\Property(property="products[1][prescription_image]", type="string", format="binary"),
     *
     *                 @OA\Property(property="packages[0][package_slug]", type="string", example="deluxe-box"),
     *                 @OA\Property(property="packages[0][quantity]", type="integer", example=1),
     *                 @OA\Property(property="packages[1][package_slug]", type="string", example="super-box"),
     *                 @OA\Property(property="packages[1][quantity]", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Your kitbag order has been placed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your kitbag order has been placed successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="amount", type="number", format="float", example=1384),
     *                 @OA\Property(property="order_number", type="string", example="1GljzaAzrncgKdEGNobV"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="date", type="string", example="2025/11/10"),
     *                 @OA\Property(property="delivery_address", type="string", example="Baisepatti, Kathmandu"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=false),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", nullable=true, example=null),
     *                 @OA\Property(property="gift_wrap_charge", type="integer", example=0),
     *                 @OA\Property(property="latitude", type="string", example="88.52144"),
     *                 @OA\Property(property="longitude", type="string", example="12.21554"),
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_name", type="string", example="Debitis quia nulla molestiae."),
     *                         @OA\Property(property="variant_name", type="string", example="Variant-1"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=183),
     *                         @OA\Property(property="total", type="number", format="float", example=366),
     *                         @OA\Property(property="prescription_image", type="string", format="url", example="http://192.168.100.23:8008/storage/2684/flowers-7382926_1920.jpg")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function kitbagOrder(KitbagRequest $request) {
        if (!$request->hasAny(['products'])) {
            return $this->apiError("At least one product or package must be included in the order.", 422);
        }
        try {
            $response = (new OrderService)->saveOrder($request, OrderTypeEnum::KITBAG);
        } catch (OrderException $e) {
            return $this->apiError($e->getMessage());
        }
        return $this->apiSuccess("Your kitbag order has been placed successfully.", $response);
    }
}
