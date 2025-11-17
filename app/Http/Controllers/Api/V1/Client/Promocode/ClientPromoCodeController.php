<?php

namespace App\Http\Controllers\Api\V1\Client\Promocode;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Promocode\UserPromoCodeResource;
use App\Models\Point\CouponCode;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ClientPromoCodeController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/check-coupon",
     *     summary="Validate a coupon code",
     *     tags={"Coupon"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 description="Coupon code to validate",
     *                 example="PROMO2025"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon is valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon is validated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The code field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Coupon not active or expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Coupon is expired.")
     *         )
     *     )
     * )
     */

    function checkcode(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);
        $promocode = CouponCode::where('code', $request->code)->where('is_active', true)->first();
        if(!$promocode){
            return $this->apiError('Coupon not valided');
        }
        $now = now();
        if ($now->lt($promocode->start_date)) {
            return $this->apiError('Coupon is not active yet.');
        } else if ($now->gt($promocode->end_date)) {
            return $this->apiError('Coupon is expired.');
        }
        return $this->apiSuccess('Coupon is valided', new UserPromoCodeResource($promocode));
    }
}
