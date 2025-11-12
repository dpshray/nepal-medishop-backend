<?php

namespace App\Http\Controllers\Api\V1\Vendor\Notification;

use App\Http\Controllers\Controller;
use App\Models\VendorNotification;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderAssignNotificationController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Get(
     *     path="/vendor/notifications",
     *     summary="Get all vendor notifications",
     *     description="Fetch all notifications for the authenticated vendor, including the unseen notification count.",
     *     tags={"Vendor Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of vendor notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor notifications fetched successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="unseen_count", type="integer", example=3),
     *                 @OA\Property(
     *                     property="notifications",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="vendor_id", type="integer", example=5),
     *                         @OA\Property(property="title", type="string", example="Order Assigned"),
     *                         @OA\Property(property="message", type="string", example="You have been assigned a new order #ORD-12345"),
     *                         @OA\Property(property="is_seen", type="boolean", example=false),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T12:45:30Z")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token"
     *     )
     * )
     */
    function getNotification()
    {
        $user = Auth::user();
        $notification = VendorNotification::where('vendor_id', $user->id)
            ->latest()
            ->get();;
        $unseenCount = VendorNotification::where('vendor_id', $user->id)
            ->where('is_seen', false)
            ->count();
        return $this->apiSuccess('Vendor notifications fetched successfully', [
            'unseen_count' => $unseenCount,
            'notifications' => $notification
        ]);
    }
    /**
     * @OA\Post(
     *     path="/vendor/notifications/{id}/seen",
     *     summary="Mark a vendor notification as seen",
     *     description="Mark a specific notification as seen for the authenticated vendor.",
     *     tags={"Vendor Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Notification ID",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as seen successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification marked as seen")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token"
     *     )
     * )
     */
    function seennotification($id)
    {
        $user=Auth::user();
        $notification = VendorNotification::where('vendor_id', $user->id)
        ->where('id', $id)
        ->first();

        $notification->update(['is_seen' => true]);
        return $this->apiSuccess('Notification marked as seen');
    }
}
