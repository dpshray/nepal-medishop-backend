<?php

namespace App\Http\Controllers\Api\V1\Admin\PromoCode;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoCode\PromoCodeRequest;
use App\Http\Requests\Admin\PromoCode\PromoCodeUpdateRequest;
use App\Http\Resources\Admin\PromoCode\AdminPromoCodeResource;
use App\Models\Point\CouponCode;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminPromoCodeControlller extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Post(
     *     path="/admin/coupon",
     *     summary="Create a new coupon",
     *     tags={"Admin Promo Code"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"code","discount_percent","start_date","end_date"},
     *             @OA\Property(property="code", type="string", example="NEWYEAR2025"),
     *             @OA\Property(property="discount_percent", type="number", format="float", example=10.5),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-11-15"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-12-15"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="description", type="string", example="new year code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon store successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Coupon store successfull")
     *         )
     *     )
     * )
     */
    function store(PromoCodeRequest $request)
    {
        $coupon = CouponCode::create($request->validated());
        return $this->apiSuccess('Coupon store successfull');
    }

    /**
     * @OA\Put(
     *     path="/admin/coupon/{coupon}",
     *     summary="Update an existing coupon",
     *     tags={"Admin Promo Code"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="coupon",
     *         in="path",
     *         required=true,
     *         description="Coupon UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="string", example="NEWYEAR2025"),
     *             @OA\Property(property="discount_percent", type="number", format="float", example=15.5),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-11-20"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-12-25"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="description", type="string", example="new year code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon update successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Coupon update successfull")
     *         )
     *     )
     * )
     */
    function update(CouponCode $coupon, PromoCodeUpdateRequest $request)
    {
        $coupon->update($request->validated());
        return $this->apiSuccess('Coupon update successfull');
    }

    /**
     * @OA\Delete(
     *     path="/admin/coupon/{coupon}",
     *     summary="Delete a coupon",
     *     tags={"Admin Promo Code"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="coupon",
     *         in="path",
     *         required=true,
     *         description="Coupon UUID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon delete successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Coupon delete successfull")
     *         )
     *     )
     * )
     */
    function destroy (CouponCode $coupon)
    {
        $coupon->delete();
        return $this->apiSuccess('Coupon delete successfull');
    }
    /**
     * @OA\Get(
     *     path="/admin/coupon",
     *     summary="List all coupons",
     *     tags={"Admin Promo Code"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         description="Number of coupons per page"
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search by coupon code"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of coupon codes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of coupon code"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    function index(Request $request)
    {
        $per_page = $request->per_page;
        $search = $request->query('search', null);
        $paginated = CouponCode::when($search != null, fn($qry) => $qry->whereLike('code', '%' . $search . '%'))
            ->latest('id')
            ->paginate($per_page);
        $coupon = $this->makePaginationResponse($paginated, fn($item) => AdminPromoCodeResource::collection($item))->data;
        return $this->apiSuccess('List of coupon code', $coupon);
    }
}
