<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\UserWishlistResource;
use App\Models\Package;
use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/wishlist/{slug}/product",
     *     summary="Toggle a wishlist status of a product",
     *     description="Toggle a wishlist status of a product.",
     *     operationId="ProductWishlistToggle",
     *     tags={"Wishlist"},
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
     *             @OA\Property(property="message", type="string", example="Item added to wishlist"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function toggleProductWishlist(Product $product)
    {
        $query = $product->wishlists();
        $user_like = $query->where('user_id', Auth::id());
        $msg = null;
        if ($user_like->exists()) {
            $user_like->delete();
            $msg = 'Item removed from wishlist';
        } else {
            $msg = 'Item added to wishlist';
            $query->create(['user_id' => Auth::id()]);
        }
        return $this->apiSuccess($msg);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/wishlist/{slug}/package",
     *     summary="Toggle a wishlist status of a package",
     *     description="Toggle a wishlist status of a package.",
     *     operationId="PackageWishlistToggle",
     *     tags={"Wishlist"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of package",
     *         @OA\Schema(type="string", example="soluta-cum-reiciendis-dolorum-sunt")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful toggle",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item added to wishlist"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function togglePackageWishlist(Package $package)
    {
        $query = $package->wishlists();
        $user_like = $query->where('user_id', Auth::id());
        $msg = null;
        if ($user_like->exists()) {
            $user_like->delete();
            $msg = 'Item removed from wishlist';
        } else {
            $msg = 'Item added to wishlist';
            $query->create(['user_id' => Auth::id()]);
        }
        return $this->apiSuccess($msg);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/wishlist-items",
     *     summary="List of wishlist items of a user.",
     *     description="List of wishlist items of a user.",
     *     operationId="WishlistList",
     *     tags={"Wishlist"},
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
     *         description="List of wishlist items.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of wishlist items."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="type", type="string", example="product"),
     *                         @OA\Property(property="name", type="string", example="Debitis debitis autem consectetur saepe."),
     *                         @OA\Property(property="slug", type="string", example="debitis-debitis-autem-consectetur-saepe"),
     *                         @OA\Property(property="rating", type="number", format="float", example=1.5),
     *                         @OA\Property(property="brand", type="string", nullable=true, example="Sanofi"),
     *                         @OA\Property(property="price", type="number", format="float", example=133.44),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=139),
     *                         @OA\Property(property="feature_image", type="string", example="http://192.168.100.23:8008/storage/91/medi-plaster.png"),
     *                         @OA\Property(property="liked", type="boolean", nullable=true, example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=4)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function myWishlist(Request $request)
    {
        $per_page = $request->query('per_page');
        $pagination = Wishlist::with(['product.cheapestVariation', 'product.media', 'product.brand', 'product.likes','package.media'])
            ->where('user_id', Auth::id())
            ->paginate($per_page);
        $data  = $this->makePaginationResponse($pagination, fn($item) => UserWishlistResource::collection($item))->data;
        return $this->apiSuccess('List of wishlist items.', $data);
    }
}
