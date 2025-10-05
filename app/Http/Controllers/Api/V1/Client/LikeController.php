<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    use ResponseTrait;
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/favourite/{slug}/product",
     *     summary="Toggle a favourite status of a product",
     *     description="Toggle a favourite status of a product.",
     *     operationId="ProductFavoutiteToggle",
     *     tags={"Favourite"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of product",
     *         @OA\Schema(type="string", example="soluta-cum-reiciendis-dolorum-sunt")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful toggle",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item added to favourite"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
    */
    function toggleProductFavourite(Product $product){
        $query = $product->likes();
        $user_like = $query->where('user_id', Auth::id()); 
        $msg = null;
        if ($user_like->exists()) {
            $user_like->delete();
            $msg = 'Item removed from favourite';
        }else{
            $msg = 'Item added to favourite';
            $query->create(['user_id' => Auth::id()]);
        }
        return $this->apiSuccess($msg);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/favourite/{slug}/package",
     *     summary="Toggle a favourite status of a package",
     *     description="Toggle a favourite status of a package.",
     *     operationId="PackageFavoutiteToggle",
     *     tags={"Favourite"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of package",
     *         @OA\Schema(type="string", example="super-box")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful toggle",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item added to favourite"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function togglePackageFavourite(Package $package){
        $query = $package->likes();
        $user_like = $query->where('user_id', Auth::id());
        $msg = null;
        if ($user_like->exists()) {
            $user_like->delete();
            $msg = 'Item removed from favourite';
        } else {
            $msg = 'Item added to favourite';
            $query->create(['user_id' => Auth::id()]);
        }
        return $this->apiSuccess($msg);
    }

}
