<?php

namespace App\Http\Controllers\Api\V1\Admin\Disclaimer;

use App\Http\Controllers\Controller;
use App\Models\Disclaimer;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminDisclaimerController extends Controller
{
    //
    use PaginationTrait, ResponseTrait;
    /**
     * @OA\Get(
     *     path="/admin/disclaimer",
     *     tags={"Disclaimer"},
     *     summary="Get disclaimer",
     *     description="Get disclaimer",
     *     operationId="DisclaimerList",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="disclaimer", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    function index()
    {
        $disclaimer = Disclaimer::first();
        return $this->apiSuccess('Disclaimer details.', $disclaimer);
    }
    /**
     * @OA\Post(
     *     path="/admin/disclaimer",
     *     tags={"Disclaimer"},
     *     summary="Create or Update Disclaimer",
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"disclaimer"},
     *
     *             @OA\Property(
     *                 property="disclaimer",
     *                 type="string",
     *                 example="This information is provided for educational purposes only."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="disclaimer", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    function store(Request $request)
    {
        $data = $request->validate([
            'disclaimer' => 'required|string',
        ]);
        $disclaimer = Disclaimer::first();
        if (!empty($disclaimer)) {
            $disclaimer->update($data);
            return $this->apiSuccess('Disclaimer updated successfully.', $disclaimer);
        } else {
            $disclaimer = Disclaimer::create($data);
            return $this->apiSuccess('Disclaimer created successfully.', $disclaimer);
        }
    }
}
