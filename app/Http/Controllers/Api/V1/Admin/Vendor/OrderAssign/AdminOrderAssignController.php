<?php

namespace App\Http\Controllers\Api\V1\Admin\Vendor\OrderAssign;

use App\Http\Controllers\Controller;
use App\Models\Purchase\Order;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminOrderAssignController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/admin/orders/{order_uuid}/assign/{user_uuid}",
     *     summary="Assign an order to a vendor using UUIDs",
     *     tags={"Order Assign"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order UUID",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="55c9af7b-e7fb-4798-b3f6-3e76edc5cf2f"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="Vendor User UUID",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="aa566e85-ce2f-4756-8a8f-d4f58a500ace"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order has been assigned successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order has been assigned to John Doe"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order or User not found"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    function AssignOrder($order_uuid, $user_uuid)
    {
        $order = Order::where('uuid', $order_uuid)->firstorfail();
        $user = User::where('uuid', $user_uuid)->firstorfail();
        $order->update(['assigned_vendor_id' => $user->id]);
        return $this->apiSuccess('order has been assign to {$user->name}');
    }
}
