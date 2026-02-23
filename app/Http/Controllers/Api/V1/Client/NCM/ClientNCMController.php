<?php

namespace App\Http\Controllers\Api\V1\Client\NCM;

use App\Http\Controllers\Controller;
use App\Services\NCM\NcmService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ClientNCMController extends Controller
{
    //
    protected $ncmService;
    use ResponseTrait;
    public function __construct(NcmService $ncmService)
    {
        $this->ncmService = $ncmService;
    }
    /**
     * Get NCM branches for dropdown
     */
    /**
     * @OA\Get(
     *     path="/ncm/get-ncmbranch",
     *     summary="Get NCM branches list",
     *     tags={"NCM"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Branch list retrieved successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function getBranches()
    {
        $result = $this->ncmService->getBranches();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
            return $this->apiSuccess('Branch list', $result);
        }
    }

    /**
     * Calculate shipping rate before assignment
     */
    /**
     * @OA\Post(
     *     path="/ncm/calculate-rate",
     *     summary="Calculate NCM shipping rate",
     *     tags={"NCM"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from_branch","to_branch","delivery_type"},
     *             @OA\Property(property="from_branch", type="string", example="KTM"),
     *             @OA\Property(property="to_branch", type="string", example="PKR"),
     *             @OA\Property(
     *                 property="delivery_type",
     *                 type="string",
     *                 enum={"Door2Door","Branch2Door","Branch2Branch","Door2Branch"},
     *                 example="Door2Door"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shipping rate calculated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function calculateRate(Request $request)
    {
        $validator = $request->validate([
            // 'from_branch' => 'required|string',
            'to_branch' => 'required|string',
            // 'delivery_type' => 'required|in:Pickup/Collect,Send,D2B,B2B'
        ]);

        $result = $this->ncmService->getShippingRate(
            'TINKUNE',
            $request->to_branch,
            'pickup/collect'
        );

        return $this->apiSuccess('Shippment Rate', $result);
    }
}
