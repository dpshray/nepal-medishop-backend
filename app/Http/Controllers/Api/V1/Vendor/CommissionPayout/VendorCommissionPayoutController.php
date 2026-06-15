<?php

namespace App\Http\Controllers\Api\V1\Vendor\CommissionPayout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CommissionPayout\VendorCommissionPayoutRequest;
use App\Services\CommissionPayout\CommissionPayoutService;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorCommissionPayoutController extends Controller
{
    //
    use PaginationTrait, ResponseTrait;
    public function __construct(private CommissionPayoutService $service) {}
    /**
     * @OA\Get(
     *     path="/vendor/commission-payout",
     *     tags={"Vendor Commission Payout"},
     *  security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="preset",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="payout_status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payout History",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function index(VendorCommissionPayoutRequest $request)
    {
        // vendor_id always comes from the authenticated session — never the request
        $vendorId = Auth::user()->id;

        $range   = $request->resolvedDateRange();
        $perPage = max(1, (int) $request->input('per_page', 25));
        $filters = $request->only(['payout_status', 'page']);

        $history = $this->service->getVendorPayoutHistory(
            $vendorId,
            $range['from'],
            $range['to'],
            $filters,
            $perPage
        );

        return $this->apiSuccess("Payout History", $history);
    }
    /**
     * @OA\Post(
     *     path="/vendor/commission-payout/request",
     *     tags={"Vendor Commission Payout"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Payout Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="payout_id", type="integer"),
     *                 @OA\Property(property="amount", type="number"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function requestPayout(VendorCommissionPayoutRequest $request)
    {
        // vendor_id always from session
        $vendorId = Auth::user()->id;
        $range = $request->resolvedDateRange();
        $result = $this->service->requestPayout(
            $vendorId,
            $range['from'],
            $range['to']
        );
        return $this->apiSuccess("Payout Request", $result);
    }
}
