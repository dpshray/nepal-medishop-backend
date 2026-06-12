<?php

namespace App\Http\Controllers\Api\V1\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report\AdminProductReportRequest;
use App\Services\Report\ProductPerformanceService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminProductReportController extends Controller
{
    //
    use ResponseTrait;
    public function __construct(private ProductPerformanceService $service) {}
    /**
     * @OA\Get(
     *     path="/admin/productperformance",
     *     summary="Product performance",
     *     tags={"Admin Report"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="path",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *         name="view",
     *         in="path",
     *         required=false,
     *         @OA\Schema(type="string",enum={"best_sellers","worst_sellers","most_returned"})
     *     ),
     *      @OA\Parameter(
     *         name="from_date",
     *         in="path",
     *         required=false,
     *         @OA\Schema(type="string",format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="path",
     *         required=false,
     *         @OA\Schema(type="string",format="date")
     *     ),
     *      @OA\Parameter(
     *         name="per_page",
     *         in="path",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product performance fetched successfully"
     *     )
     * )
     */
    public function index(AdminProductReportRequest $request)
    {
        $range   = $request->resolvedDateRange();
        $view    = $request->input('view', 'best_sellers');
        $perPage = max(1, (int) $request->input('per_page', 25));

        $filters = $request->only([
            'vendor_id',
            'category_id',
            'page',
            'search'
        ]);
        $table = $this->service->getTable($range['from'], $range['to'], $filters, $view, $perPage);
        return $this->apiSuccess('Product performance fetched successfully', $table);
    }
}
