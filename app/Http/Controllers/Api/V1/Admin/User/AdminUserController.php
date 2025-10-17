<?php

namespace App\Http\Controllers\Api\V1\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\AdminUserDetailResource;
use App\Http\Resources\Admin\User\AdminUserListResource;
use App\Models\User;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    //
    use PaginationTrait, ResponseTrait;
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     summary="Get list of users",
     *     description="Retrieve a paginated list of users with total items purchased and total purchase amount.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search users by name, email, or mobile number",
     *         required=false,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="mobile_number", type="string", example="9800000000"),
     *                         @OA\Property(property="status", type="boolean", example=true),
     *                         @OA\Property(property="total_orders", type="integer", example=3),
     *                         @OA\Property(property="total_items_purchased", type="integer", example=12),
     *                         @OA\Property(property="total_purchase_amount", type="number", format="float", example=4500.50)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=5),
     *                 @OA\Property(property="total_items", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->query('search', null);
        $query = User::with(['orders.orderItems'])->where('user_type', 3);

        // Search by name, email, or mobile_number
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }
        $query->orderBy('id', 'desc');
        $paginated = $query->paginate($perPage);
        $data = $paginated->map(function ($user) {
            $totalItems = $user->orders->flatMap->orderItems->sum('quantity');
            $totalAmount = $user->orders->sum('price');

            return [
                'id' => $user->id,
                'uuid'=>$user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'status' => (bool) $user->status,
                'total_orders' => $user->orders->count(),
                'total_items_purchased' => $totalItems,
                'total_purchase_amount' => (float) $totalAmount,
            ];
        });
        $result = $this->makePaginationResponse($paginated, fn() => $data)->data;
        return $this->apiSuccess('User retrieved successfully.', $result);
    }
    /**
     * @OA\Get(
     *     path="/admin/users/{user}",
     *     summary="Get full user detail including all orders and order items",
     *     description="Retrieve user profile with all related orders and order item details.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="uuid of the user",
     *         @OA\Schema(type="string", example="86d5804e-da22-4a54-ba98-17446f0300f6")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User detail retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "name": "John Doe",
     *                     "email": "john@example.com",
     *                     "mobile_number": "9800000000",
     *                     "status": true,
     *                     "email_verified": "Verified",
     *                     "orders": {
     *                         {
     *                             "order_id": 12,
     *                             "order_code": "ORD-10012",
     *                             "price": 2999.99,
     *                             "payment_method": "COD",
     *                             "payment_status": "paid",
     *                             "status": "delivered",
     *                             "created_at": "2025-10-15 14:20:00",
     *                             "items": {
     *                                 {
     *                                     "item_type": "Product",
     *                                     "quantity": 2,
     *                                     "price": 1499.99,
     *                                     "total": 2999.98,
     *                                     "product_name": "Shampoo",
     *                                     "variant_name": "500ml"
     *                                 }
     *                             }
     *                         }
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    function show(User $user)
    {
        $user->load([
            'orders.orderItems.product',
            'orders.orderItems.productVariant'
        ]);
        $data = new AdminUserDetailResource($user);
        return $this->apiSuccess('User detail retrieved successfully.', $data);
    }
}
