<?php

namespace App\Http\Controllers\Api\V1\Admin\Purchase\NCM;

use App\Http\Controllers\Controller;
use App\Models\Purchase\NcmOrder;
use App\Models\Purchase\Order;
use App\Services\NCM\NcmService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminNCMOrderController extends Controller
{
    //
    protected $ncmService;
    use ResponseTrait;
    public function __construct(NcmService $ncmService)
    {
        $this->ncmService = $ncmService;
    }
    /**
     * Assign Order to NCM
     *
     * @OA\Post(
     *     path="/admin/ncm/assign-to-ncm/{uuid}",
     *     summary="Assign order to NCM courier",
     *     tags={"Admin NCM Orders"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Order UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fbranch","tbranch","delivery_type"},
     *
     *             @OA\Property(
     *                 property="fbranch",
     *                 type="string",
     *                 example="Kathmandu"
     *             ),
     *             @OA\Property(
     *                 property="tbranch",
     *                 type="string",
     *                 example="Pokhara"
     *             ),
     *             @OA\Property(
     *                 property="delivery_type",
     *                 type="string",
     *                 enum={"Door2Door","Branch2Door","Branch2Branch","Door2Branch"},
     *                 example="Door2Door"
     *             ),
     *             @OA\Property(
     *                 property="weight",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 example=1.5
     *             ),
     *             @OA\Property(
     *                 property="package",
     *                 type="string",
     *                 nullable=true,
     *                 example="Box"
     *             ),
     *             @OA\Property(
     *                 property="instruction",
     *                 type="string",
     *                 nullable=true,
     *                 example="Handle with care"
     *             ),
     *             @OA\Property(
     *                 property="phone2",
     *                 type="string",
     *                 nullable=true,
     *                 example="9800000001"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order successfully assigned to NCM"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or already assigned"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to assign order to NCM"
     *     )
     * )
     */

    function assign_to_ncm(Request $request, $uuid)
    {
        $data = $request->validate([
            'fbranch' => 'required|string',
            'tbranch' => 'required|string',
            'delivery_type' => 'required|in:Door2Door,Branch2Door,Branch2Branch,Door2Branch',
            'weight' => 'nullable|numeric|min:0.1',
            'package' => 'nullable|string|max:255',
            'instruction' => 'nullable|string|max:500',
        ]);
        try {
            DB::beginTransaction();

            $order = Order::where('uuid', $uuid)->first();
            // Log::info('NCM Order: ' . json_encode($order));
            // Check if already assigned
            $existingNcmOrder = NcmOrder::where('order_id', $order->id)->first();
            if ($existingNcmOrder && $existingNcmOrder->ncm_order_id) {
                return $this->apiError('Order already assigned to NCM');
            }
            $result = $this->ncmService->getShippingRate(
                $request->fbranch,
                $request->tbranch,
                'Pickup/Collect'
            );
            // Log::info('NCM Shipping Rate: ' . json_encode($result));
            $order->price = $order->price - $order->delivery_charge;
            $order->price = $order->price + $result['data']['charge'];
            $order->delivery_charge = $result['data']['charge'];
            $order->save();
            $ncmData = [
                'name' => $order->name,
                'phone' => $order->mobile,
                'phone2' => $request->phone2 ?? '',
                'cod_charge' => (string) $order->price,
                'address' => $order->address,
                'fbranch' => $request->fbranch,
                'branch' => $request->tbranch,
                'package' => $request->package ?? null,
                'vref_id' => 'ORD-' . $order->order_code,
                'instruction' => $request->instruction ?? '',
                'delivery_type' => $request->delivery_type,
                'weight' => $request->weight ?? null
            ];
            $result = $this->ncmService->createOrder($ncmData);
            // return $result;
            // Log::info('NCM Order Result: ' . json_encode($result));
            if (!$result['success']) {
                DB::rollBack();
                return $this->apiError('Failed to create order in NCM');
            }
            $ncmOrder = NcmOrder::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'ncm_order_id' => $result['data']['orderid'] ?? null,
                    'fbranch' => $ncmData['fbranch'],
                    'tbranch' => $ncmData['branch'],
                    'package' => $ncmData['package'],
                    'weight' => $ncmData['weight'],
                    'cod_charge' => $ncmData['cod_charge'],
                    'delivery_charge' => $order->delivery_charge,
                    'instruction' => $ncmData['instruction'],
                    'delivery_status' => 'Pickup Order Created',
                    'delivery_type' => $ncmData['delivery_type']
                ]
            );
            if ($order->status === 'PENDING') {
                $order->update(['status' => 'SHIPPED']);
                $order->save();
            }
            DB::commit();
            return $this->apiSuccess('Order successfully assigned to NCM');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin Assign NCM Order Error: ' . $e->getMessage());
            return $this->apiError(
                'Failed to assign order to NCM: ' . $e->getMessage(),
                500
            );
        }
    }
}
