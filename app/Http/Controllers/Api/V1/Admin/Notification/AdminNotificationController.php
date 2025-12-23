<?php

namespace App\Http\Controllers\Api\V1\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Notification\AdminNotificationDetailResource;
use App\Http\Resources\Admin\Notification\AdminNotificationListResource;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    use PaginationTrait, ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/notification",
     *     summary="Admin notification list",
     *     description="Get grievance list.",
     *     operationId="AdminNotificationList",
     *     tags={"AdminNotification"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number of list",
     *         @OA\Schema(type="integer", example=1)
     *     ),     
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items on each page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search vendor based on user name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin notification list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin notification list"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="uuid", type="string", example="1b4148ae-4899-4344-8e64-8a5ed5aa0ec1"),
     *                         @OA\Property(property="subject", type="string", example="New Order Received – Order #r4c2x7"),
     *                         @OA\Property(property="date", type="string", example="2025/12/23"),
     *                         @OA\Property(property="read_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="type", type="string", example="Order")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
    */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $per_page = $per_page ? $per_page : Auth::user()->notifications->count(); 
        $pagination = Auth::user()->notifications()->paginate($per_page);
        $notifications = $this->makePaginationResponse($pagination, fn($item) => AdminNotificationListResource::collection($item))->data;
        return $this->apiSuccess('Admin notification list', $notifications);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/notification/{uuid}",
     *     summary="Admin notification detail",
     *     description="Get grievance detail.",
     *     operationId="AdminNotificationDetail",
     *     tags={"AdminNotification"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of notification",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin notification list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin notification list"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="uuid", type="string", example="1b4148ae-4899-4344-8e64-8a5ed5aa0ec1"),
     *                         @OA\Property(property="subject", type="string", example="New Order Received – Order #r4c2x7"),
     *                         @OA\Property(property="date", type="string", example="2025/12/23"),
     *                         @OA\Property(property="read_at", type="string", nullable=true, example=null)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
    */
    public function show($notification_uuid)
    {
        $notification = DatabaseNotification::findOrFail($notification_uuid);
        $notification->markAsRead();
        $notification_detail = new AdminNotificationDetailResource($notification);
        return $this->apiSuccess('Notification detail', $notification_detail);
    }
}
