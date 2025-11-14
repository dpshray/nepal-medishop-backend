<?php

namespace App\Http\Controllers\Api\V1\Admin\Point;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Point\AdminCouponRequest;
use Illuminate\Http\Request;

class AdminCouponPointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/coupon-point",
     *     summary="Store a coupon.",
     *     description="Store a coupon.",
     *     operationId="CouponCodeStore",
     *     tags={"CouponCode"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"coupon_code", "discount_percent", "is_active", "start_date", "end_date"},
     *                 @OA\Property(property="coupon_code", type="string", example="MERCK10"),
     *                 @OA\Property(property="description", type="string", example="10% off on all Merck products"),
     *                 @OA\Property(property="discount_percent", type="number", format="float", example=10.00),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-11-12"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2025-12-12")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category create response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(AdminCouponRequest $request)
    {
        return $request->all();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
