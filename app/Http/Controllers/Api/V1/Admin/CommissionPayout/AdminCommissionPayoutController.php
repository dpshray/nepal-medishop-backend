<?php

namespace App\Http\Controllers\Api\V1\Admin\CommissionPayout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommissionPayout\AdminCommissionPayoutRequest;
use App\Models\Payout\VendorPayout;
use App\Services\CommissionPayout\CommissionPayoutService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCommissionPayoutController extends Controller
{
    //
    use ResponseTrait;
    public function __construct(private CommissionPayoutService $service) {}
    /**
     * @OA\Get(
     *     path="/admin/commission-payout",
     *     tags={"Commission Payout"},
     *     summary="Commission Payout Report",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-01-01")
     *     ),
     *
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-01-31")
     *     ),
     *
     *     @OA\Parameter(
     *         name="preset",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={
     *                 "today",
     *                 "yesterday",
     *                 "last_7_days",
     *                 "last_30_days",
     *                 "this_month",
     *                 "last_month",
     *                 "this_year",
     *                 "custom"
     *             },
     *             example="last_30_days"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="payout_status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"PENDING","PROCESSING","PAID","REJECTED"},
     *             example="PENDING"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={10,25,50,100},
     *             example=25
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Commission payout report",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Commission Payout Report"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function index(AdminCommissionPayoutRequest $request)
    {
        $range   = $request->resolvedDateRange();
        $perPage = max(1, (int) $request->input('per_page', 25));

        $filters = $request->only(['vendor_id', 'payout_status', 'page']);

        $table = $this->service->getAdminTable(
            $range['from'],
            $range['to'],
            $filters,
            $perPage
        );
        return $this->apiSuccess('Commission Payout Report', $table);
    }

    // GET /admin/reports/commission-payout/{vendorId}/orders
    // Per-vendor drill-down to order-level breakdown
    /**
     * @OA\Get(
     *     path="/admin/commission-payout/{vendorId}/orders",
     *     tags={"Commission Payout"},
     *     summary="Vendor Commission Drill Down",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="vendorId",
     *         in="path",
     *         required=true,
     *         description="Vendor ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="preset",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={
     *                 "today",
     *                 "yesterday",
     *                 "last_7_days",
     *                 "last_30_days",
     *                 "this_month",
     *                 "last_month",
     *                 "this_year",
     *                 "custom"
     *             }
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", enum={10,25,50,100})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor order breakdown",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor Drill Down"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function vendorCommissionPayoutRequest(AdminCommissionPayoutRequest $request, int $vendorId)
    {
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

        return $this->apiSuccess("Vendor Commission Payout Request", $history);
    }

    // PATCH /admin/reports/commission-payout/{payout}
    // Admin updates a payout status: processing / paid / rejected
    /**
     * @OA\Patch(
     *     path="/admin/commission-payout/{payout}",
     *     tags={"Commission Payout"},
     *     summary="Update Payout Status",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="payout",
     *         in="path",
     *         required=true,
     *         description="Payout ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"processing","paid","rejected"}, example="processing"),
     *             @OA\Property(property="remarks", type="string", example="Processing the payout request")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payout updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payout updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, VendorPayout $payout)
    {
        $request->validate([
            'status'  => ['required', 'in:processing,paid,rejected'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->service->updatePayoutStatus(
            payout: $payout,
            status: $request->input('status'),
            remarks: $request->input('remarks'),
            processedBy: Auth::user()->id,
        );

        if (!$result['success']) {
            return $this->apiError('Commission Payout Report', $result['message'], 422);
        }

        return $this->apiSuccess('Commission Payout Report', $result);
    }
}
