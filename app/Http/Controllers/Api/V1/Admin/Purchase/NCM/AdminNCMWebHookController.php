<?php

namespace App\Http\Controllers\Api\V1\Admin\Purchase\NCM;

use App\Http\Controllers\Controller;
use App\Models\Purchase\NcmOrder;
use App\Models\Purchase\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminNCMWebHookController extends Controller
{
    //
    public function WebHook(Request $request)
    {
        // if ($request->query('token') !== config('services.ncm.webhook_secret')) {
        //     Log::warning('Invalid NCM Webhook Token', [
        //         'ip' => $request->ip()
        //     ]);

        //     // Always return 200 to stop retries
        //     return response()->json(['success' => false], 200);
        // }
        Log::info('NCM Webhook Received', $request->all());

        //Validate required fields
        if (!$request->has(['order_id', 'status'])) {
            Log::warning('NCM Webhook Invalid Payload', $request->all());
            return response()->json(['error' => 'Invalid payload'], 200);
        }

        $orderId = $request->order_id;
        $status  = $request->status;

        //Find NCM order safely
        $ncmOrder = NcmOrder::where('ncm_order_id', $orderId)->first();

        if (!$ncmOrder) {
            Log::warning('NCM Order Not Found', ['order_id' => $orderId]);
            return response()->json(['success' => true], 200);
        }

        //actual order
        $order = Order::find($ncmOrder->order_id);

        if (!$order) {
            Log::warning('Order Not Found', ['order_id' => $ncmOrder->order_id]);
            return response()->json(['success' => true], 200);
        }

        //map NCM status to your system
        $order->status = $status;
        $order->save();

        return response()->json(['success' => true], 200);
    }
}
