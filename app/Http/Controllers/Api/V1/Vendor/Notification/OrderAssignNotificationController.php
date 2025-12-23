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
    function getNotification()
    {
        $user = Auth::user();
        $notification = VendorNotification::where('vendor_id', $user->id)
            ->latest()
            ->get();
        $unseenCount = VendorNotification::where('vendor_id', $user->id)
            ->where('is_seen', false)
            ->count();
        return $this->apiSuccess('Vendor notifications fetched successfully', [
            'unseen_count' => $unseenCount,
            'notifications' => $notification
        ]);
    }
     
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
