<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Notifications\SavePushNotification;
use App\Services\PushNotificationService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPushNotificationController extends Controller
{
    use ResponseTrait;

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/notify/client",
     *     summary="Send notification to student(verified) based on ther exam type.",
     *     description="Send notification to student(verified) based on ther exam type.",
     *     operationId="BulkNotification",
     *     tags={"Notification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "body"},
     *             @OA\Property(property="title", type="string", example="This is a title."),
     *             @OA\Property(property="body", type="string", example="This is a description."),
     *             @OA\Property(property="send_and_store", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification response data",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Notification send with 1 success and 0 failure")
     *         )
     *     )
     * )
     */
    function pushNotifiyClient(Request $request) {
        $form_data = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'send_and_store' => 'sometimes|nullable|boolean'
        ]);

        $send_and_store = (array_key_exists('send_and_store', $form_data) && filter_var($form_data['send_and_store'], FILTER_VALIDATE_BOOLEAN) == true) ? true : false;
        $PNS =new PushNotificationService($form_data['title'], $form_data['body']);
        if ($send_and_store) {
            $PNS->store();
        }
        $fcm_tokens = DB::table('users')
            ->where('user_type', 3)
            ->whereNull('deleted_at')
            ->whereNotNull('fcm_token')
            ->where('status', 1)
            ->distinct()
            ->get();
        [
            'successes' => $success,
            'failures' => $failure
        ] = $PNS->notify($fcm_tokens);
        return $this->apiSuccess("Notification sent with $success success and $failure");
    }
}
