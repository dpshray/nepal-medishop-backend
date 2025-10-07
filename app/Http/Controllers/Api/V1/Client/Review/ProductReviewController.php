<?php

namespace App\Http\Controllers\Api\V1\Client\Review;

use App\Http\Controllers\Api\V1\Client\ClientController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Product\Review\ClientProductReviewRequest;
use App\Http\Resources\User\Review\ProductReviewListResource;
use App\Models\Product;
use App\Models\Review;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class ProductReviewController extends ClientController
{
    use PaginationTrait, ResponseTrait;

    function __construct()
    {
        $this->middleware(['auth:sanctum'])->only(['store','update','destroy']);
    }
    /**
     * @OA\Get(
     *     path="/product/{slug}/review",
     *     summary="Get reviews based on product slug.",
     *     description="Get reviews based on product slug.",
     *     operationId="ProductReviewList",
     *     tags={"ProductReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of product",
     *         @OA\Schema(type="string", example="pubg-sleeves-confortable-finger")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Api page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Review fetched successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="comment_uuid", type="string", format="uuid", example="972f4163-b858-4715-bca3-a8fac6ae2475"),
     *                         @OA\Property(property="user_name", type="string", example="vendor25391021"),
     *                         @OA\Property(property="review", type="string", example="Some product review another user"),
     *                         @OA\Property(property="rating", type="integer", example=3),
     *                         @OA\Property(
     *                             property="user_type",
     *                             type="object",
     *                             @OA\Property(property="user_type", type="integer", example=2),
     *                             @OA\Property(property="label", type="string", example="VENDOR")
     *                         ),
     *                         @OA\Property(property="review_date", type="string", example="07 Oct 2025"),
     *                         @OA\Property(property="is_review_edited", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    function index(Request $request, Product $product) {
        $per_page = $request->query('per_page', 10);
        $pagination = $product->reviews()->with(['user'])->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => ProductReviewListResource::collection($item))->data;
        return $this->apiSuccess('Review fetched successfully.', $data);
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/product/{slug}/review",
     *     summary="Store a product review",
     *     description="Store a product review.",
     *     operationId="StoreProductReview",
     *     tags={"ProductReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a product",
     *         @OA\Schema(type="string", example="maccoffee-original-coffee")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"review","rating"},
     *             @OA\Property(property="review", type="string", example="Some product review"),
     *             @OA\Property(property="rating", type="integer", example=5),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item reviewed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item reviewed successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *         )
     *     )
     * )
     */
    function store(ClientProductReviewRequest $request, Product $product){
        if ($product->reviews()->where('user_id',Auth::id())->exists()) {
            return $this->apiError('You have already submitted a review for this item.',409);
        }
        $data = $request->safe()->merge(['user_id' => Auth::id()])->all();
        $product->reviews()->create($data);
        return $this->apiSuccess('Item reviewed successfully.');
    }

    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/product/{slug}/review/{uuid}",
     *     summary="Update a review belonging to a user.",
     *     description="Update a review belonging to a user.",
     *     operationId="UpdateProductReview",
     *     tags={"ProductReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a product",
     *         @OA\Schema(type="string", example="maccoffee-original-coffee")
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a review",
     *         @OA\Schema(type="string", example="972f4163-b858-4715-bca3-a8fac6ae2459")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"review","rating"},
     *             @OA\Property(property="review", type="string", example="Some product review"),
     *             @OA\Property(property="rating", type="integer", example=5),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review has been updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Review has been updated."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *         )
     *     )
     *   )
     * )
     */
    function update(ClientProductReviewRequest $request, Product $product, Review $review) {
        throw_if($review->user->isNot(Auth::user()), UnauthorizedException::class);
        $data = $request->safe()->merge(['user_id' => Auth::id()])->all();
        $product->reviews()->firstWhere('uuid', $review->uuid)->update($data);
        return $this->apiSuccess('Review has been updated.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}},
     *     path="/product/{slug}/review/{uuid}",
     *     summary="Delete a review belonging to a user.",
     *     description="Delete a review belonging to a user.",
     *     operationId="DeleteProductReview",
     *     tags={"ProductReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a product",
     *         @OA\Schema(type="string", example="maccoffee-original-coffee")
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a review",
     *         @OA\Schema(type="string", example="972f4163-b858-4715-bca3-a8fac6ae2459")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review has been removed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Review has been removed."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *         )
     *     )
     *   )
     * )
     */
    function destroy(Product $product, Review $review) {
        throw_if($review->user->isNot(Auth::user()), UnauthorizedException::class);
        $product->reviews()->firstWhere('uuid', $review->uuid)->delete();
        return $this->apiSuccess('Review has been removed.');
    }
}
