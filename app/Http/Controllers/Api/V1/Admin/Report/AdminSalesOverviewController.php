<?php

namespace App\Http\Controllers\Api\V1\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report\AdminSalesOverviewRequest;
use App\Services\Report\SalesOverviewService;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminSalesOverviewController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    public function __construct(private SalesOverviewService $service) {}
    /**
     * @OA\Get(
     *     path="/admin/reports/sales-overview",
     *     tags={"Admin Report"},
     *     summary="Sales Overview Report",
     *     description="Returns sales summary cards, charts, and paginated table data.",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date (required when preset=custom)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-01-01")
     *     ),
     *
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date (required when preset=custom)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2026-01-31")
     *     ),
     *
     *     @OA\Parameter(
     *         name="preset",
     *         in="query",
     *         required=false,
     *         description="Predefined date range",
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
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Parameter(
     *         name="district",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="Kathmandu")
     *     ),
     *
     *     @OA\Parameter(
     *         name="payment_method",
     *         in="query",
     *         required=false,
     *         description="Payment method used for order",
     *         @OA\Schema(type="string", example="COD")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={
     *                 "PENDING",
     *                 "PROCESSING",
     *                 "SHIPPED",
     *                 "DELIVERED",
     *                 "CANCELLED",
     *                 "RETURNED"
     *             },
     *             example="DELIVERED"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="group_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"day","week","month"},
     *             example="day"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             example=1
     *         )
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
     *         description="Sales overview report",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="date_from", type="string", example="2026-01-01 00:00:00"),
     *                 @OA\Property(property="date_to", type="string", example="2026-01-31 23:59:59"),
     *                 @OA\Property(property="group_by", type="string", example="day"),
     *                 @OA\Property(property="generated_at", type="string"),
     *                 @OA\Property(property="data_freshness", type="string", example="real_time")
     *             ),
     *
     *             @OA\Property(
     *                 property="summary_cards",
     *                 type="object"
     *             ),
     *
     *             @OA\Property(
     *                 property="charts",
     *                 type="object"
     *             ),
     *
     *             @OA\Property(
     *                 property="table",
     *                 type="object"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function index(AdminSalesOverviewRequest $request)
    {
        $range   = $request->resolvedDateRange();
        $groupBy = $request->groupBy();
        $perPage = (int)$request->per_page;

        $filters = $request->only([
            'vendor_id',
            'category_id',
            'payment_method',
            'status',
            'page',
        ]);

        $cards    = $this->service->getSummaryCards($range['from'], $range['to'], $filters);
        $trend    = $this->service->getRevenueTrend($range['from'], $range['to'], $groupBy, $filters);
        $category = $this->service->getSalesByCategory($range['from'], $range['to'], $filters);
        $district = $this->service->getSalesByDistrict($range['from'], $range['to'], $filters);
        $table    = $this->service->getDetailTable($range['from'], $range['to'], $filters, $perPage);

        return response()->json([
            'meta' => [
                'date_from'   => $range['from']->setTimezone('Asia/Kathmandu')->toDateTimeString(),
                'date_to'     => $range['to']->setTimezone('Asia/Kathmandu')->toDateTimeString(),
                'group_by'    => $groupBy,
                'generated_at' => now('Asia/Kathmandu')->toDateTimeString(),
                // Tell the frontend whether figures are real-time or from
                // the summary table (update this once nightly rollup is built)
                'data_freshness' => 'real_time',
            ],
            'summary_cards' => $cards,
            'charts' => [
                'revenue_trend'   => $this->formatTrendForChart($trend, 'gmv'),
                'orders_trend'    => $this->formatTrendForChart($trend, 'order_count'),
                'sales_by_category' => $category,
                // 'sales_by_district' => $district,
            ],
            'table' => $table,
        ]);
    }
    private function formatTrendForChart(array $trend, string $valueKey): array
    {
        return array_map(fn($row) => [
            'label' => $row->period,
            'value' => round((float) $row->$valueKey, 2),
        ], $trend);
    }
}
