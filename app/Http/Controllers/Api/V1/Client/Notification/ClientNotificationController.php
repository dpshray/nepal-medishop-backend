<?php

namespace App\Http\Controllers\Api\V1\Client\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Notification\UserNotificationListResource;
use App\Models\User;
use App\Notifications\SavePushNotification;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class ClientNotificationController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/notification",
     *     summary="User notification list",
     *     description="User notification list",
     *     operationId="UserNotificationList",
     *     tags={"UserNotification"},
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
     *                         @OA\Property(property="type", type="string", example="VENDOR_PRODUCT_APPROVAL")
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
        $pagination = DatabaseNotification::where('notifiable_type', User::class)
            ->whereIn('type', [SavePushNotification::class])
            ->latest()
            ->paginate($per_page);
        $notifications = $this->makePaginationResponse($pagination, fn($item) => UserNotificationListResource::collection($item))->data;
        return $this->apiSuccess('User notification list', $notifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
