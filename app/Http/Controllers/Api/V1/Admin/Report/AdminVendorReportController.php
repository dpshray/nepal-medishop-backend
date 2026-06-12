<?php

namespace App\Http\Controllers\Api\V1\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report\AdminVendorReportRequest;
use App\Services\Report\VendorPerformanceService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminVendorReportController extends Controller
{
    //
    use ResponseTrait;
    public function __construct(private VendorPerformanceService $service) {}
    /**
     * @OA\Get(
     *  path="/admin/vendorperformance",
     *  tags={"Admin Report"},
     *  security={ {"sanctum": {} } },
     *  @OA\Parameter(
     *      name="date_from",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="string", format="date")
     *  ),
     *  @OA\Parameter(
     *      name="date_to",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="string", format="date")
     *  ),
     *  @OA\Parameter(
     *      name="preset",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string",
     *          enum={"today","yesterday","last_7_days","last_30_days","this_month","last_month","this_year","custom"}
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="search",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="string")
     *  ),
     *  @OA\Parameter(
     *      name="vendor_status",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string",
     *          enum={"active","suspended"}
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="category_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Parameter(
     *      name="sort_by",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string",
     *          enum={"store_name","total_orders","gmv","net_revenue","commission","fulfillment_rate","cancellation_rate","return_rate"}
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="sort_dir",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string",
     *          enum={"asc","desc"}
     *      )
     *  ),
     *  @OA\Parameter(
     *      name="page",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Parameter(
     *      name="per_page",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Vendor leaderboard fetch successfully",
     *      @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="Vendor leaderboard fetch successfully"),
     *          @OA\Property(property="data", type="array", 
     *              @OA\Items(
     *                  @OA\Property(property="vendor_id", type="integer", example=1),
     *                  @OA\Property(property="store_name", type="string", example="Vendor Store"),
     *                  @OA\Property(property="district", type="string", example="Kathmandu"),
     *                  @OA\Property(property="verified_at", type="string", example="2022-01-01T00:00:00.000000Z"),
     *                  @OA\Property(property="owner_name", type="string", example="Vendor Owner"),
     *                  @OA\Property(property="commission_percentage", type="number", example=15),
     *                  @OA\Property(property="total_orders", type="integer", example=10),
     *                  @OA\Property(property="total_items", type="integer", example=20),
     *                  @OA\Property(property="gmv", type="number", example=1000.00),
     *                  @OA\Property(property="net_revenue", type="number", example=1000.00),
     *                  @OA\Property(property="commission", type="number", example=150.00),
     *                  @OA\Property(property="fulfillment_rate", type="number", example=80.00),
     *                  @OA\Property(property="cancellation_rate", type="number", example=5.00),
     *                  @OA\Property(property="flags", type="array",
     *                      @OA\Items(type="string", example="cancellation_rate")
     *                  ),
     *                  @OA\Property(property="is_underperformer", type="boolean", example=false)
     *              )
     *          )
     *      )
     *  )
     * )
     */
    public function index(AdminVendorReportRequest $request)
    {
        $range   = $request->resolvedDateRange();
        $perPage = max(1, (int) $request->input('per_page', 25));
        $sortBy  = $request->input('sort_by',  'gmv');
        $sortDir = $request->input('sort_dir', 'desc');

        $filters = $request->only([
            'search',
            'vendor_status',
            'category_id',
            'page',
        ]);

        $leaderboard = $this->service->getLeaderboard(
            $range['from'],
            $range['to'],
            $filters,
            $sortBy,
            $sortDir,
            $perPage
        );
        return $this->apiSuccess('Vendor leaderboard fetch successfully', $leaderboard);
    }
}
