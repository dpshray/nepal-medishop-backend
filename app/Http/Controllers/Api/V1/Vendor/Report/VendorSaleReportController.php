<?php

namespace App\Http\Controllers\Api\V1\Vendor\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Report\VendorSaleReportRequest;
use App\Services\Report\Vendor\VendorSalesService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorSaleReportController extends Controller
{
    //
    use ResponseTrait;
    public function __construct(private VendorSalesService $service) {}
    /**
     * Get vendor sale report
     * @OA\Get(
     *     path="/vendor/sale-report",
     *     tags={"Vendor Sale Report"},
     *     security={{
     *         "sanctum": {}
     *     }},
     *     summary="Get vendor sale report",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         description="Start date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         description="End date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="group_by",
     *         in="query",
     *         required=false,
     *         description="Group by",
     *         @OA\Schema(type="string", enum={"day", "week", "month"})
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="Category id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         required=false,
     *         description="Product id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="order_status",
     *         in="query",
     *         required=false,
     *         description="Order status",
     *         @OA\Schema(type="string", enum={"PENDING", "ASSIGNED", "PROCESSING", "READY_FOR_DISPATCH", "DISPATCHED", "DELIVERED", "CANCELLED", "NOT_COMPLETELY_BATCHED"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Vendor sale report fetched successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="summary_cards", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="charts", type="object",
     *                     @OA\Property(property="revenue_trend", type="array",
     *                         @OA\Items(type="object")
     *                     ),
     *                     @OA\Property(property="top_products", type="array",
     *                         @OA\Items(type="object")
     *                     )
     *                 ),
     *                 @OA\Property(property="table", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", example=1),
     *                     @OA\Property(property="to", type="integer", example=25),
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="data", type="array",
     *                         @OA\Items(type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(VendorSaleReportRequest $request)
    {
        // vendor_id always from session — never from the request
        $vendorId = Auth::user()->id;

        $range   = $request->resolvedDateRange();
        $groupBy = $request->groupBy();
        $perPage = max(1, (int) $request->input('per_page', 25));

        $filters = $request->only([
            'category_id',
            'product_id',
            'order_status',
            'page',
        ]);

        $cards      = $this->service->getSummaryCards($vendorId, $range['from'], $range['to'], $filters);
        $trend      = $this->service->getRevenueTrend($vendorId, $range['from'], $range['to'], $groupBy, $filters);
        $topProducts = $this->service->getTopProducts($vendorId, $range['from'], $range['to'], $filters);
        $table      = $this->service->getDetailTable($vendorId, $range['from'], $range['to'], $filters, $perPage);

        return $this->apiSuccess('Vendor sale report fetched successfully', [
            'summary_cards' => $cards,
            'charts' => [
                'revenue_trend' => $trend,
                'top_products'  => $topProducts,
            ],
            'table' => $table,
        ]);
    }
}
