<?php

namespace App\Http\Controllers\Api\V1\Admin\Vendor\OrderAssign;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Purchase\Order;
use App\Models\User;
use App\Models\VendorProductPrice;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminOrderAssignController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\get(
     *     path="/admin/orders/{order_uuid}/assign/{user_uuid}",
     *     summary="Assign an order to a vendor using UUIDs",
     *     tags={"Order Assign"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order_uuid",
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
     *         name="user_uuid",
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
    public function AssignOrder($order_uuid, $user_uuid)
    {
        $order = Order::where('uuid', $order_uuid)
            ->with('orderItems') // load order items
            ->firstOrFail();

        $user = User::where('uuid', $user_uuid)->firstOrFail();

        // Loop
        foreach ($order->orderItems as $item) {
            // Skip check for packages
            if ($item->item_type === Package::class) {
                continue;
            }

            // For product, check vendor's stock
            $vendorProduct =VendorProductPrice::where([
                'product_vendor_id' => $user->id,
                'product_variation_id' => $item->item_variant_id,
                'status' => 1, // approved by admin
            ])->first();

            if (!$vendorProduct) {
                return $this->apiError("Vendor does not have the product variation (ID: {$item->item_variant_id}) approved for sale.");
            }

            if ($vendorProduct->units_in_stock < $item->quantity) {
                return $this->apiError("Vendor does not have enough stock for product variation (ID: {$item->item_variant_id}).");
            }
        }

        $order->update(['assigned_vendor_id' => $user->id]);

        return $this->apiSuccess("Order has been assigned to {$user->name}");
    }

    /**
     * @OA\Post(
     *     path="/admin/order/{order_uuid}/cancel-assign",
     *     summary="Cancel the assignment of an order to a vendor",
     *     tags={"Order Assign"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order_uuid",
     *         in="path",
     *         required=true,
     *         description="Order UUID",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="55c9af7b-e7fb-4798-b3f6-3e76edc5cf2f"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order assignment canceled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order assignment canceled successfully"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function CancelAssignOrder($order_uuid)
    {
        $order = Order::where('uuid', $order_uuid)->firstOrFail();
        $order->update(['assigned_vendor_id' => null]);
        return $this->apiSuccess('Order assignment canceled successfully');
    }
}
