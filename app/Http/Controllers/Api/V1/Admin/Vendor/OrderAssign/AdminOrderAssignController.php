<?php

namespace App\Http\Controllers\Api\V1\Admin\Vendor\OrderAssign;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Vendor\Order\AdminVendorAssignabilityList;
use App\Models\Package;
use App\Models\Purchase\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorProductPrice;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminOrderAssignController extends Controller
{
    //
    use ResponseTrait;
    use PaginationTrait;

    /**
     * @OA\Get(
     *     path="/admin/orders/{order_uuid}/vendors",
     *     summary="Get list of vendors with assignability status for a specific order",
     *     description="Returns a list of vendors along with a boolean property `is_assignable` indicating whether the order can be assigned to that vendor.",
     *     tags={"Order Assign"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="order_uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order to check assignable vendors for",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="55c9af7b-e7fb-4798-b3f6-3e76edc5cf2f"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="search vendor based on username and store name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of vendors with order assignability status.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of vendors with order assignability status"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="user_name", type="string", example="vendor00"),
     *                         @OA\Property(property="store_name", type="string", example="Schumm Ltd"),
     *                         @OA\Property(property="vendor_uuid", type="string", format="uuid", example="7fdd51b5-795c-4f01-8484-709eea4e2e77"),
     *                         @OA\Property(property="is_assignable", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function getVendorsWithAssignability(Request $request, Order $order)
    {
        /**
         * Conditions of assignability:
         * - Vendor must be verified and user must be verified and active
         * - vendor_products status: 1
         * - vendor_product_prices status: 1
         * - units_in_stock must be greater than order quantity
         */

        $order->load('orderItems');
        $order_uuid = $order->uuid;
        $search = $request->query('search');

        // 1️⃣ Collect order items and total quantities (including packages)
        $user_order = DB::select("
        SELECT 
            u.item_variant_id,
            SUM(u.quantity) AS total_quantity,
            o.id AS order_id
        FROM (
            SELECT 
                oi.order_id,
                oi.item_type,
                oi.item_variant_id,
                oi.quantity
            FROM order_items AS oi
            WHERE oi.item_type != 'App\\\\Models\\\\Package'

            UNION ALL

            SELECT  
                oi.order_id,
                oi.item_type,
                pp.product_variation_id AS item_variant_id,
                (oi.quantity * pp.quantity) AS quantity
            FROM order_items AS oi
            JOIN packages AS p 
                ON oi.item_slug = p.slug
            JOIN package_products AS pp 
                ON p.id = pp.package_id
            WHERE oi.item_type = 'App\\\\Models\\\\Package'
        ) AS u
        JOIN orders AS o
            ON u.order_id = o.id
        WHERE o.uuid = ?
        GROUP BY u.item_variant_id, o.id
    ", [$order_uuid]);

        $user_order = collect($user_order)->keyBy('item_variant_id');

        // 2️⃣ Load vendors with related user, products, and prices
        $vendors = Vendor::VerifiedAndActive()
            ->select('id', 'uuid', 'user_id', 'store_name')
            ->with([
                'user',
                'vendorProducts' => fn($qry) => $qry
                    ->select('id', 'status', 'is_approved', 'vendor_id')
                    ->with([
                        'vendorPrices' => fn($q) => $q
                            ->select('status', 'product_vendor_id', 'product_variation_id', 'units_in_stock')
                            ->active()
                    ])
                    ->active()
            ])
            ->when($search, function ($qry) use ($search) {
                $qry->where(function ($sub) use ($search) {
                    $sub->where('store_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQry) use ($search) {
                            $userQry->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->get();

        // 3️⃣ Filter only assignable vendors
        $assignable_vendors = $vendors->filter(function ($vendor) use ($user_order) {
            foreach ($user_order as $variant_id => $order_item) {
                $has_variant = $vendor->vendorProducts->contains(function ($product) use ($variant_id, $order_item) {
                    $prices = $product->vendorPrices->where('product_variation_id', $variant_id);
                    return $prices->isNotEmpty() && $prices->first()->units_in_stock > $order_item->total_quantity;
                });

                if (!$has_variant) {
                    return false; // If any variant not available, vendor is not assignable
                }
            }

            return true;
        });

        // 4️⃣ Format data — return all (no pagination)
        $items = $assignable_vendors->map(function ($vendor) {
            return [
                'user_name' => $vendor->user->name,
                'store_name' => $vendor->store_name,
                'vendor_uuid' => $vendor->uuid,
                'is_assignable' => true,
            ];
        })->values();

        $data = [
            'items' => $items,
            'total_items' => $items->count(),
        ];

        return $this->apiSuccess('List of vendors with order assignability status', $data);
    }


    /**
     * @OA\get(
     *     path="/admin/order/{order_uuid}/assign/{vendor_uuid}",
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
     *         name="vendor_uuid",
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
    public function AssignOrder($order_uuid, $vendor_uuid)
    {
        $order = Order::where('uuid', $order_uuid)
            ->with('orderItems') // load order items
            ->firstOrFail();
        $vendor = Vendor::with(['vendorProductPrices','user'])->where('uuid', $vendor_uuid)->firstOrFail();
        // Loop
        foreach ($order->orderItems as $item) {
            // Skip check for packages
            if ($item->item_type === Package::class) {
                continue;
            }

            // For product, check vendor's stock
            /* $vendorProduct = VendorProductPrice::where([
                'product_vendor_id' => $user->id,
                'product_variation_id' => $item->item_variant_id,
                'status' => 1, // approved by admin
            ])->first(); */
            /* $vendorProduct = $vendor->vendorProductPrices()->active()->where([
                'product_variation_id' => $item->item_variant_id
            ])->first(); */

            $vendorProduct = $vendor->vendorProductPrices()
                ->where('vendor_product_prices.product_variation_id', $item->item_variant_id)
                ->where('vendor_product_prices.status', 1)
                ->first();

            if (!$vendorProduct) {
                return $this->apiError("Vendor does not have the product variation (ID: {$item->item_variant_id}) approved for sale.");
            }

            if ($vendorProduct->units_in_stock < $item->quantity) {
                return $this->apiError("Vendor does not have enough stock for product variation (ID: {$item->item_variant_id}).");
            }
            $total_stock = $vendorProduct->units_in_stock - $item->quantity;
            $vendorProduct->update(['units_in_stock' => $total_stock]);
        }
        $order->update(['assigned_vendor_id' => $vendor->user->id]);
        return $this->apiSuccess("Order has been assigned to {$vendor->user->name}");
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
        $order = Order::where('uuid', $order_uuid)->with('orderItems')->firstOrFail();
        $vendor = User::findOrFail($order->assigned_vendor_id)->vendor;
        $vendor->load('vendorProductPrices');

        foreach ($order->orderItems as $item) {
            if ($item->item_type === Package::class) {
                continue;
            }

            $vendorProduct = $vendor->vendorProductPrices()
                ->where('vendor_product_prices.product_variation_id', $item->item_variant_id)
                ->where('vendor_product_prices.status', 1) // qualified table name
                ->first();

            if ($vendorProduct) {
                $total_stock = $vendorProduct->units_in_stock + $item->quantity;
                $vendorProduct->update(['units_in_stock' => $total_stock]);
            }
        }

        $order->update(['assigned_vendor_id' => null]);

        return $this->apiSuccess('Order assignment canceled successfully');
    }
}
