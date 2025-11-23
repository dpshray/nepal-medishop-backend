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
use Illuminate\Support\Facades\Auth;
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
     *         description="Vendors capable of fulfilling at least part of your order.",
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
        $atleast_one_order_item_any_vendors = $AAV_service->vendorsThatCanFulfillOneItem($order);
        $total_items = count($atleast_one_order_item_any_vendors);
        $items = AdminVendorOrderAssignListResource::collection($atleast_one_order_item_any_vendors);
        return $this->apiSuccess('Vendors capable of fulfilling at least part of your order', compact('total_items','items'));
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
        $vendor = Vendor::where('uuid', $vendor_uuid)->firstOrFail();
        $order_items_ids = $request->order_items_ids;
        $order = Order::with([
            'orderItems' => fn($qry) => $qry->whereIn('id', $order_items_ids)
            ])
            ->where('uuid', $order_uuid)
            ->firstOrFail();
        $items = $order->orderItems->map(fn($OI) => [
            "item_name" => $OI['item_name'],
            "item_price" => (float)$OI['price'],
            "quantity" => $OI['quantity'],
            "sub_total" => (float)$OI['total'],
        ]);
        // return $request->all();
        $result = (new AssignOrderToVendorService)->canVendorFulfillAllItems($order, $order_items_ids, $vendor);
        if (!$result['eligible']) {
            return $this->apiError('Assignment failed: the vendor does not have enough stock for one or more order items.',422, $result['failed_items']);
        }
        $order->orderItems()->whereIn('id', $order_items_ids)->update(['assigned_vendor_id' => $vendor->id]);
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

    /**
     * @OA\Post(
     *     security={{"sanctum":{}}},
     *     path="/admin/order/{uuid}/assign-to-admin",
     *     summary="Assign an order to an admin using UUIDs",
     *     operationId="OrderAssignToAdmin",
     *     tags={"Order Assign"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of an order.",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="dee559ea-c25c-4263-b24f-560fe9c8a22d"
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
    function AssignOrderToAdmin(Request $request, Order $order) {
        $request->validate([
            'order_items_ids' => 'required|array',
            'order_items_ids.*' => 'required|exists:order_items,id'
        ], [
            'order_items_ids.*.exists' => 'The selected order items ids is invalid.'
        ]);
        $vendor = Auth::user()->vendor;
        if (empty($vendor)) {
            return $this->apiError('This admin is not associated to vendor',422);
        }
        $order_items_ids = $request->order_items_ids;
        // return $request->all();
        try {
            $items = (new AssignOrderToVendorService)->assignOrderToVendor($vendor, $order, $order_items_ids);
        } catch (\App\Exceptions\AssignOrderException $e) {
            return $this->apiError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->apiError('Something went wrong while assigning order');
        }
        $vendor_name = $vendor->user->name;
        $vendor_store_name = $vendor->store_name;
        return $this->apiSuccess("Order has been assigned to admin", compact('items', 'vendor_name', 'vendor_store_name'));
    }
}
