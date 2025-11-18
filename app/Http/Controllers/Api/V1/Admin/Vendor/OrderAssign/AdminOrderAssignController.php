<?php

namespace App\Http\Controllers\Api\V1\Admin\Vendor\OrderAssign;

use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Vendor\Order\AdminVendorAssignabilityList;
use App\Http\Resources\Admin\Vendor\Order\AdminVendorOrderAssignListResource;
use App\Models\Package;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorNotification;
use App\Models\VendorProductPrice;
use App\Services\AssignOrderToVendorService;
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
     *         description="search vendor based on store name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of vendors with order assignability status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="List of vendors with order assignability status"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="total_items",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="vendor_uuid",
     *                             type="string",
     *                             example="a382ab1b-1152-47cb-92e1-99ce9d64781f"
     *                         ),
     *                         @OA\Property(
     *                             property="vendor_location",
     *                             type="string",
     *                             example="2453 Milan Plaza Suite 289\nMullerchester, IL 69167-0480"
     *                         ),
     *                         @OA\Property(
     *                             property="store_name",
     *                             type="string",
     *                             example="Bosco PLC"
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     )
     * )
     */
    public function getVendorsWithAssignability(Request $request, Order $order)
    {
        $AAV_service = (new AssignOrderToVendorService);
        $AAV_service->search = $request->query('search');
        $matchedVendors = $AAV_service->fetchEligibleVendors($order->orderItems->pluck('id')->all());
        $total_items = count($matchedVendors);
        $items = AdminVendorOrderAssignListResource::collection($matchedVendors);
        return $this->apiSuccess('List of vendors with order assignability status', compact('total_items','items'));
    }


    /**
     * @OA\Post(
     *     security={{"sanctum":{}}},
     *     path="/admin/order/{order_uuid}/assign/{vendor_uuid}",
     *     summary="Assign an order to a vendor using UUIDs",
     *     operationId="OrderAssignToVendor",
     *     tags={"Order Assign"},
     *     @OA\Parameter(
     *         name="order_uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of an order.",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="dee559ea-c25c-4263-b24f-560fe9c8a22d"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="vendor_uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a vendor.",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="efa981ff-6095-4976-9d8a-d415e62832a4"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="order_items_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order has been assigned to admin",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order has been assigned to admin"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="item_name",
     *                             type="string",
     *                             example="Dr Rashel VitaminC Sun screen SPF 50+++ 200g"
     *                         ),
     *                         @OA\Property(
     *                             property="item_price",
     *                             type="number",
     *                             format="float",
     *                             example=3375
     *                         ),
     *                         @OA\Property(
     *                             property="quantity",
     *                             type="integer",
     *                             example=2
     *                         ),
     *                         @OA\Property(
     *                             property="sub_total",
     *                             type="number",
     *                             format="float",
     *                             example=6750
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_name",
     *                     type="string",
     *                     example="sCHOLAR ltd "
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_store_name",
     *                     type="string",
     *                     example="Bosco PLC"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     )
     * )
     */
    public function AssignOrder(Request $request, $order_uuid, $vendor_uuid)
    {
        $request->validate([
            'order_items_ids' => 'required|array',
            'order_items_ids.*' => 'required|exists:order_items,id'
        ],[
            'order_items_ids.*.exists' => 'The selected order items ids is invalid.'
        ]);
        $order_items_ids = $request->order_items_ids;
        $order = Order::where('uuid', $order_uuid)->firstOrFail();
        if ($order->is_order_completely_assigned) {
            return $this->apiError('This order has already been assigned');
        }
        
        $vendor = Vendor::where('uuid', $vendor_uuid)->firstOrFail();
        // return $request->all();
        /**
         * verifying that wether all incoming order_items_is belongs to this order
         */
        $order_qry = $order->orderItems();
        $order_items = $order_qry->whereIn('id', $order_items_ids)
            ->get();
        if ($order_items->count() != count($order_items_ids)) {
            return $this->apiError('Order items does not exists in this order');
        }

        $AAV_service = new AssignOrderToVendorService;
        $AAV_service->vendor_id = $vendor->id;
        // return $request->order_items_ids;
        $res = $AAV_service->fetchEligibleVendors($order_items_ids);
        $product_item_variant_id_w_quantity = $AAV_service->product_item_variant_id_w_quantity;
        /**
         * rechecking that this vendor have sufficient stock to meet this order items
         */
        if (count($res) <= 0) {
            return $this->apiError('Assignment failed: vendor inventory is insufficient for these items.');
        }
        // return 'OK';
        DB::transaction(function () use($order, $order_items_ids, $vendor, $product_item_variant_id_w_quantity){
            $order->orderItems()->whereIn('id', $order_items_ids)->update(['assigned_vendor_id' => $vendor->id]);
            $vendor->vendorProductPrices()
                ->whereIn('product_variation_id', $product_item_variant_id_w_quantity->pluck('item_variant_id')->all())
                ->each(function($item) use($product_item_variant_id_w_quantity){
                    $quantity = $product_item_variant_id_w_quantity->firstWhere('item_variant_id', $item->product_variation_id)['quantity'];
                    # reducing units_in_stock from vendor
                    $item->decrement('units_in_stock', $quantity);
                });
            $order->refresh();
            $all_order_hasBeen_assigned = $order->orderItems->whereNull('assigned_vendor_id')->isEmpty();
            if ($all_order_hasBeen_assigned) {
                $order->update(['is_order_completely_assigned' => true]);
            }
        });
        $items = $order_items_for_response = $order_items->map(function($item){
            return [
                'item_name' => $item['item_name'],
                'item_price' => (float)$item['price'],
                'quantity' => (int)$item['quantity'],
                'sub_total' => (float) $item['total']
            ];
        });
        $vendor_name = $vendor->user->name;
        $vendor_store_name = $vendor->store_name;
        return $this->apiSuccess("Order has been assigned to {$vendor_name}", compact('items','vendor_name','vendor_store_name'));
    }

    /**
     * @OA\Post(
     *     path="/admin/order/{uuid}/cancel-assign",
     *     summary="Cancel the assignment of an order to a vendor",
     *     tags={"Order Assign"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="uuid",
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
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Order assignment canceled successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 nullable=true,
     *                 example=null
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     )
     * )
     */
    public function CancelAssignOrder(Order $order)
    {
        if ($order->payment_status == PaymentStatusEnum::PAID) {
            return $this->apiSuccess('Order has already been paid.');
        }else if ($order->status == OrderStatusEnum::DELIVERED || $order->status == OrderStatusEnum::SHIPPED) {
            return $this->apiSuccess('Order has already been delivered.');
        }
        $order->load('orderItems.assignedVendor.vendorProductPrices');
        // return $order;
        DB::transaction(function () use($order){
            $AOTVService = new AssignOrderToVendorService;
            $order_items = $order->orderItems;
            $products_to_handle = $AOTVService->transformOrderItemsIntoProducts($order_items->pluck('id')->all());
            foreach ($order_items as $OI) {
                $qty_to_increase = $products_to_handle->firstWhere('item_variant_id', $OI->item_variant_id)['quantity'];
                $OI->assignedVendor
                    ->vendorProductPrices()
                    ->firstWhere('product_variation_id', $OI->item_variant_id)
                    ->increment('units_in_stock', $qty_to_increase);
            }
            $order->update(['status' => OrderStatusEnum::CANCELLED]);
        });

        return $this->apiSuccess('Order assignment canceled successfully');
    }
}
