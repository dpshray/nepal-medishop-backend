<?php

namespace App\Http\Controllers\Api\V1\Client\Purchase;

use App\Enums\Purchase\OrderTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Purchase\CODRequest;
use App\Services\OrderService;
use App\Traits\ResponseTrait;

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
     *         @OA\JsonContent(
     *             required={"payment_method","name","email","mobile","address","products"},
     *             @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *             @OA\Property(property="name", type="string", example="James P. Sullivan"),
     *             @OA\Property(property="email", type="string", format="email", example="james.sullivan100@example.com"),
     *             @OA\Property(property="mobile", type="string", example="9854112547"),
     *             @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *             @OA\Property(property="description", type="string", example="some description of this order COD"),
     *             @OA\Property(property="gift_wrap", type="boolean", example=true),
     *             @OA\Property(property="gift_wrap_remarks", type="boolean", example="gift wrap must be in silver paper."),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_slug","variant_id","quantity"},
     *                     @OA\Property(property="product_slug", type="string", example="unde-a-maiores-et-omnis"),
     *                     @OA\Property(property="variant_id", type="integer", example=2),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="packages",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"package_slug","quantity"},
     *                     @OA\Property(property="package_slug", type="string", example="deluxe-box"),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Your order has been placed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your order has been placed successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="amount", type="number", format="float", example=2248.46),
     *                 @OA\Property(property="order_number", type="string", example="7gnhQMRGxGZORSC8OuNb"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="date", type="string", example="2025/10/30"),
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_name", type="string", example="Quo voluptas quam dolorum voluptas."),
     *                         @OA\Property(property="variant_name", type="string", example="Variant-2"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=1124.23),
     *                         @OA\Property(property="total", type="number", format="float", example=2248.46)
     *                     )
     *                 ),
     *                 @OA\Property(property="delivery_address", type="string", example="Boudha, Kathmandu"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper with golden ribbon"),
     *                 @OA\Property(property="gift_wrap_charge", type="integer", example=300)
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

        $response = (new OrderService)->saveOrder($request, OrderTypeEnum::REGULAR);

        return $this->apiSuccess("Your order has been placed successfully.", $response);
    }

    /**
     * @OA\Post(
     *     path="/kitbag-orders",
     *     summary="Submit an order(Kitbag).",
     *     description="Submit an order.NOTE: name, email, mobile fields are only needed for GUEST USER.",
     *     operationId="CODKitbagOrder",
     *     tags={"Order"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method","name","email","mobile","address","products"},
     *             @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *             @OA\Property(property="name", type="string", example="James P. Sullivan"),
     *             @OA\Property(property="email", type="string", format="email", example="james.sullivan100@example.com"),
     *             @OA\Property(property="mobile", type="string", example="9854112547"),
     *             @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *             @OA\Property(property="description", type="string", example="some description of this order COD"),
     *             @OA\Property(property="gift_wrap", type="boolean", example=true),
     *             @OA\Property(property="gift_wrap_remarks", type="boolean", example="gift wrap must be in silver paper."),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_slug","variant_id","quantity"},
     *                     @OA\Property(property="product_slug", type="string", example="unde-a-maiores-et-omnis"),
     *                     @OA\Property(property="variant_id", type="integer", example=2),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="packages",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"package_slug","quantity"},
     *                     @OA\Property(property="package_slug", type="string", example="deluxe-box"),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Your order has been placed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your order has been placed successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="amount", type="number", format="float", example=2248.46),
     *                 @OA\Property(property="order_number", type="string", example="7gnhQMRGxGZORSC8OuNb"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="date", type="string", example="2025/10/30"),
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_name", type="string", example="Quo voluptas quam dolorum voluptas."),
     *                         @OA\Property(property="variant_name", type="string", example="Variant-2"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=1124.23),
     *                         @OA\Property(property="total", type="number", format="float", example=2248.46)
     *                     )
     *                 ),
     *                 @OA\Property(property="delivery_address", type="string", example="Boudha, Kathmandu"),
     *                 @OA\Property(property="gift_wrap", type="boolean", example=true),
     *                 @OA\Property(property="gift_wrap_remarks", type="string", example="gift wrap must be in silver paper with golden ribbon"),
     *                 @OA\Property(property="gift_wrap_charge", type="integer", example=300)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function kitbagOrder(CODRequest $request) {
        if (!$request->hasAny(['products', 'packages'])) {
            return $this->apiError("At least one product or package must be included in the order.", 422);
        }

        $response = (new OrderService)->saveOrder($request, OrderTypeEnum::KITBAG);

        return $this->apiSuccess("Your order has been placed successfully.", $response);
    }
}
