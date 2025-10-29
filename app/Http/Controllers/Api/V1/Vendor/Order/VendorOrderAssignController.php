<?php

namespace App\Http\Controllers\Api\V1\Vendor\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\Order\OrderAssignDetailResource;
use App\Http\Resources\Vendor\Order\OrderAssignListResource;
use App\Models\Purchase\Order;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $per_page = $request->query('per_page', 10);
        $search = $request->query('search');
        $user = Auth::user();
        $pagination = Order::where('assigned_vendor_id', $user->id)
            ->with(['user'])
            ->withCount('orderItems')
            ->when($search, function ($qry) use ($request) {
                $qry->where(function ($q) use ($request) {
                    $q->whereLike('name', '%' . $request->search . '%')
                        ->orWhereLike('email', '%' . $request->search . '%')
                        ->orWhereLike('mobile', '%' . $request->search . '%')
                        ->orWhereLike('address', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => OrderAssignListResource::collection($item))->data;
        return $this->apiSuccess('List of Assign Orders.', $data);
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
     *         description="Order detail retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order Detail."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_id", type="integer", example=12),
     *                 @OA\Property(property="order_code", type="string", example="ORD-2025-001"),
     *                 @OA\Property(property="price", type="number", format="float", example=2500.75),
     *                 @OA\Property(property="payment_method", type="string", example="esewa"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="customer_name", type="string", example="John Doe"),
     *                 @OA\Property(property="address", type="string", example="Kathmandu, Nepal"),
     *                 @OA\Property(property="order_items", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="product_name", type="string", example="Laptop Pro 15"),
     *                         @OA\Property(property="variant_name", type="string", example="8GB / 256GB"),
     *                         @OA\Property(property="quantity", type="integer", example=1),
     *                         @OA\Property(property="price", type="number", format="float", example=1200.50),
     *                         @OA\Property(property="total", type="number", format="float", example=1200.50),
     *                         @OA\Property(property="featured_image", type="string", example="https://example.com/image.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    function show(Order $order)
    {
        $order = new OrderAssignDetailResource($order);
        return $this->apiSuccess('Order Detail.', $order);
    }
    /** @OA\Put(
     *     path="/vendor/orders/{order}",
     *     summary="Update the status of an assigned order",
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
     *                 enum={"Processing", "Shipped", "Delivered"},
     *                 example="Processing"
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
        $request->validate([
            'status' => 'required|string|in:Processing,Shipped,Delivered'
        ]);
        $order->update(['status' => $request->status]);
        return $this->apiSuccess('Order has been update successfull');
    }
}
