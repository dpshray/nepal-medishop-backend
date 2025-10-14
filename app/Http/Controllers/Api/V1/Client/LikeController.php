<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\UserLikeResource;
use App\Models\Like;
use App\Models\Package;
use App\Models\Product;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    use ResponseTrait, PaginationTrait;
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
     *     path="/liked-products",
     *     summary="List of liked products of a user.",
     *     description="List of liked products of a user.",
     *     operationId="ProductFavoutiteList",
     *     tags={"Favourite"},
     *     @OA\Parameter(
     *         name="page",
     *         in="path",
     *         required=false,
     *         description="Pagination page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="path",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=1)
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
    function myLikedProducts(Request $request) {
        $per_page = $request->query('per_page');
        $pagination = Like::with(['product.cheapestVariation','product.media','product.brand'])
            ->where('user_id', Auth::id())
            ->paginate($per_page);
        $data  = $this->makePaginationResponse($pagination, fn($item) => UserLikeResource::collection($item))->data;
        return $this->apiSuccess('List of liked items.', $data);
    }
}
