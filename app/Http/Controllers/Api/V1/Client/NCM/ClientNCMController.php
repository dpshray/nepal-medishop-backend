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
     *     path="/ncm/calculate-delivery-charge",
     *     summary="Calculate NCM shipping rate",
     *     tags={"NCM"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to_branch"},
     *             @OA\Property(property="to_branch", type="string", example="PKR"),
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
            'Pickup/Collect'
        );

        return $this->apiSuccess('Shippment Rate', $result);
    }
}
