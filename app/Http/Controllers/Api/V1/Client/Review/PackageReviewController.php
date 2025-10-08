<?php

namespace App\Http\Controllers\Api\V1\Client\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Product\Review\ClientProductReviewRequest;
use App\Http\Resources\User\Review\PackageReviewListResource;
use App\Http\Resources\User\Review\ProductReviewListResource;
use App\Models\Package;
use App\Models\Review;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

class PackageReviewController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    function __construct()
    {
        $this->middleware(['auth:sanctum'])->only(['store', 'update', 'destroy']);
    }
    /**
     * @OA\Get(
     *     path="/package/{slug}/review",
     *     summary="Get reviews based on package slug.",
     *     description="Get reviews based on package slug.",
     *     operationId="packageReviewList",
     *     tags={"PackageReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of package",
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
     *         description="Review fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Review fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="comment_uuid", type="string", example="08435868-74ba-419c-8788-6c647868ec97"),
     *                         @OA\Property(property="user_name", type="string", example="user00"),
     *                         @OA\Property(property="review", type="string", example="I think.' And she squeezed herself up on tiptoe..."),
     *                         @OA\Property(property="rating", type="integer", example=5),
     *                         @OA\Property(
     *                             property="user_type",
     *                             type="object",
     *                             @OA\Property(property="user_type", type="integer", example=3),
     *                             @OA\Property(property="label", type="string", example="USER")
     *                         ),
     *                         @OA\Property(property="review_date", type="string", example="02 Oct 2025"),
     *                         @OA\Property(property="is_review_edited", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=3),
     *                 @OA\Property(property="total_items", type="integer", example=25)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function index(Request $request, Package $package)
    {
        $per_page = $request->query('per_page', 10);
        $pagination = $package->reviews()->with(['user'])->latest()->paginate($per_page);

        $reviews = $this->makePaginationResponse($pagination, fn($item) => PackageReviewListResource::collection($item))->data;
        return $this->apiSuccess('Review fetched successfully.', $reviews);
    }
    /**
     * @OA\Get(
     *     path="/fetch-package-ratings/{slug}",
     *     summary="Get ratings based on package slug.",
     *     description="Get ratings based on package slug.",
     *     operationId="packageRatings",
     *     tags={"PackageReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of package",
     *         @OA\Schema(type="string", example="pubg-sleeves-confortable-finger")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="package ratings fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="package ratings fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="rating", type="integer", example=5),
     *                     @OA\Property(property="total_raters", type="integer", example=9)
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function getPackageRatingsByAllUser(Package $package)
    {
        $ratings = DB::table('reviews')
            ->select('rating', DB::raw('count(*) as total_raters'))
            ->where([
                ['reviewable_type', Package::class],
                ['reviewable_id', $package->id],
            ])
            ->groupBy('rating')
            ->orderBy('rating', 'DESC')
            ->get()
            ->map(fn($item) => ['rating' => (int) $item->rating, 'total_raters' => (int) $item->total_raters]);
        $total_raters = (int) $ratings->sum('total_raters');
        $averageRating = $total_raters > 0
            ? round($ratings->reduce(function ($carry, $item) {
                return $carry + ($item['rating'] * $item['total_raters']);
            }, 0) / $total_raters, 2)
            : 0;
        $avg_rating = (float) round($averageRating, 2);
        return $this->apiSuccess('Package ratings fetched successfully.', compact('ratings', 'total_raters', 'avg_rating'));
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/package/{slug}/review",
     *     summary="Store a package review",
     *     description="Store a package review.",
     *     operationId="StorepackageReview",
     *     tags={"PackageReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a package",
     *         @OA\Schema(type="string", example="maccoffee-original-coffee")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"review","rating"},
     *             @OA\Property(property="review", type="string", example="Some package review"),
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
    function store(ClientProductReviewRequest $request, Package $package)
    {
        if ($package->reviews()->where('user_id', Auth::id())->exists()) {
            return $this->apiError('You have already submitted a review for this package.', 409);
        }
        $data = $request->safe()->merge(['user_id' => Auth::id()])->all();
        $package->reviews()->create($data);
        return $this->apiSuccess('Package reviewed successfully.');
    }
    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/package/{slug}/review/{uuid}",
     *     summary="Update a review belonging to a user.",
     *     description="Update a review belonging to a user.",
     *     operationId="UpdatePackageReview",
     *     tags={"PackageReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a package",
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
     *             @OA\Property(property="review", type="string", example="Some package review"),
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
    function update(ClientProductReviewRequest $request, Package $package, Review $review)
    {
        throw_if($review->user->isNot(Auth::user()), UnauthorizedException::class);
        $data = $request->safe()->merge(['user_id' => Auth::id()])->all();
        $package->reviews()->firstWhere('uuid', $review->uuid)->update($data);
        return $this->apiSuccess('Review has been updated.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}},
     *     path="/package/{slug}/review/{uuid}",
     *     summary="Delete a review belonging to a user.",
     *     description="Delete a review belonging to a user.",
     *     operationId="DeletePackageReview",
     *     tags={"PackageReview"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a package",
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
    function destroy(Package $package, Review $review)
    {
        throw_if($review->user->isNot(Auth::user()), UnauthorizedException::class);
        $package->reviews()->firstWhere('uuid', $review->uuid)->delete();
        return $this->apiSuccess('Review has been removed.');
    }
}
