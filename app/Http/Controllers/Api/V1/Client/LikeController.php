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
     *     path="/liked-items",
     *     summary="List of liked products of a user.",
     *     description="List of liked products of a user.",
     *     operationId="ProductFavoutiteList",
     *     tags={"Favourite"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Pagination page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of liked items.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of liked items."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string", example="Debitis debitis autem consectetur saepe."),
     *                         @OA\Property(property="slug", type="string", example="debitis-debitis-autem-consectetur-saepe"),
     *                         @OA\Property(property="brand", type="string", example="Sanofi"),
     *                         @OA\Property(property="rating", type="number", format="float", example=1),
     *                         @OA\Property(property="price", type="number", format="float", example=133.44),
     *                         @OA\Property(property="previous_price", type="number", format="float", example=139),
     *                         @OA\Property(property="discount_percent", type="number", format="float", example=4),
     *                         @OA\Property(property="feature_image", type="string", format="url", example="http://192.168.100.23:8008/storage/91/medi-plaster.png"),
     *                         @OA\Property(
     *                             property="variations",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="variation_id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Variant-1"),
     *                                 @OA\Property(property="size_value", type="number", example=100),
     *                                 @OA\Property(property="size_unit", type="string", example="patch"),
     *                                 @OA\Property(property="price", type="number", example=139),
     *                                 @OA\Property(property="previous_price", type="number", nullable=true, example=null)
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=5),
     *                 @OA\Property(property="total_items", type="integer", example=5)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function myLikedItems(Request $request) {
        $per_page = $request->query('per_page');
        $pagination = Like::with(['product.cheapestVariation','product.media','product.brand', 'product.variations'])
            ->where('user_id', Auth::id())
            ->paginate($per_page);
        $data  = $this->makePaginationResponse($pagination, fn($item) => UserLikeResource::collection($item))->data;
        return $this->apiSuccess('List of liked items.', $data);
    }
}
